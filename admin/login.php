<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_admin_logged_in()) {
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

$error = '';
$warning = '';
$isLockedOut = false;
$maxAttempts = 5;
$lockoutMinutes = 15;

$db = get_db();
$clientIp = get_client_ip();

// Check if IP is currently locked out
$lockStmt = $db->prepare("
    SELECT COUNT(*) as failed_count, 
           MAX(attempted_at) as last_attempt,
           TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(MAX(attempted_at), INTERVAL ? MINUTE)) as seconds_remaining 
    FROM admin_login_attempts 
    WHERE ip_address = ? AND is_success = 0 AND attempted_at > (NOW() - INTERVAL ? MINUTE)
");
$lockStmt->execute([$lockoutMinutes, $clientIp, $lockoutMinutes]);
$lockStatus = $lockStmt->fetch();
$failedCount = (int)($lockStatus['failed_count'] ?? 0);
$secondsRemaining = (int)($lockStatus['seconds_remaining'] ?? 0);

if ($failedCount >= $maxAttempts && $secondsRemaining > 0) {
    $minutesRemaining = max(1, (int)ceil($secondsRemaining / 60));
    $isLockedOut = true;
    $error = "Too many failed login attempts from this network. Access is locked for security. Please try again in {$minutesRemaining} minute(s).";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Re-check lockout for both IP and specific username
    if (!$isLockedOut) {
        $userLockStmt = $db->prepare("
            SELECT COUNT(*) as failed_count, 
                   MAX(attempted_at) as last_attempt,
                   TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(MAX(attempted_at), INTERVAL ? MINUTE)) as seconds_remaining 
            FROM admin_login_attempts 
            WHERE (ip_address = ? OR username = ?) AND is_success = 0 AND attempted_at > (NOW() - INTERVAL ? MINUTE)
        ");
        $userLockStmt->execute([$lockoutMinutes, $clientIp, $username, $lockoutMinutes]);
        $userLockStatus = $userLockStmt->fetch();
        $userFailedCount = (int)($userLockStatus['failed_count'] ?? 0);
        $userSecsRemaining = (int)($userLockStatus['seconds_remaining'] ?? 0);

        if ($userFailedCount >= $maxAttempts && $userSecsRemaining > 0) {
            $minutesRemaining = max(1, (int)ceil($userSecsRemaining / 60));
            $isLockedOut = true;
            $error = "Too many failed login attempts for this account or IP. Access is temporarily locked for security. Please try again in {$minutesRemaining} minute(s).";
        }
    }

    if ($isLockedOut) {
        // Locked out, reject attempt
    } elseif (!empty($username) && !empty($password)) {
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Check if active
            if (isset($admin['is_active']) && (int)$admin['is_active'] === 0) {
                $error = 'Your administrator account has been deactivated. Please contact a Super Admin.';
                // Log failed attempt
                $db->prepare("INSERT INTO admin_login_attempts (ip_address, username, is_success) VALUES (?, ?, 0)")->execute([$clientIp, $username]);
            } else {
                // Successful login
                $db->prepare("INSERT INTO admin_login_attempts (ip_address, username, is_success) VALUES (?, ?, 1)")->execute([$clientIp, $username]);
                // Clear previous failed attempts
                $db->prepare("DELETE FROM admin_login_attempts WHERE (ip_address = ? OR username = ?) AND is_success = 0")->execute([$clientIp, $username]);
                // Update last login
                $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);

                $_SESSION['admin_user_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_full_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = $admin['role'];

                set_flash('success', "Welcome back, " . htmlspecialchars($admin['full_name']) . "!");
                header('Location: ' . BASE_URL . '/admin/index.php');
                exit;
            }
        } else {
            // Failed credentials - record attempt
            $db->prepare("INSERT INTO admin_login_attempts (ip_address, username, is_success) VALUES (?, ?, 0)")->execute([$clientIp, $username]);

            // Re-count failed attempts
            $newCountStmt = $db->prepare("
                SELECT COUNT(*) FROM admin_login_attempts 
                WHERE (ip_address = ? OR username = ?) AND is_success = 0 AND attempted_at > (NOW() - INTERVAL ? MINUTE)
            ");
            $newCountStmt->execute([$clientIp, $username, $lockoutMinutes]);
            $currentFailed = (int)$newCountStmt->fetchColumn();
            $attemptsRemaining = max(0, $maxAttempts - $currentFailed);

            if ($attemptsRemaining <= 0) {
                $isLockedOut = true;
                $error = "Account locked for 15 minutes due to {$maxAttempts} consecutive failed login attempts.";
            } else {
                $error = "Invalid username or password. <strong>{$attemptsRemaining} attempt" . ($attemptsRemaining === 1 ? '' : 's') . " remaining</strong> before a 15-minute security lockout.";
            }
        }
    } else {
        $error = 'Please enter your username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Login | <?= APP_NAME ?></title>
    <!-- Favicon & Touch Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/images/favicon.png"/>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/favicon.ico"/>
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>/assets/images/apple-touch-icon.png"/>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#004cb6',
                        'primary-container': '#0b63e5',
                        'secondary-container': '#fe6b00'
                    },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }
                }
            }
        };
    </script>
</head>
<body class="bg-slate-900 text-slate-100 font-sans min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-800/90 backdrop-blur-xl border border-slate-700/60 rounded-3xl p-8 shadow-2xl">
        <div class="text-center mb-8">
            <img src="<?= BASE_URL ?>/assets/images/logo-card.png" alt="EzSafar Logo" class="w-20 h-20 mx-auto rounded-3xl shadow-xl mb-3 object-contain border border-slate-700/60"/>
            <h1 class="text-2xl font-black tracking-tight text-white">EZ<span class="text-orange-400">Safar</span> Control Center</h1>
            <p class="text-xs text-slate-400 mt-1">Sign in with your staff or administrator credentials</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-5 p-3.5 rounded-xl <?= $isLockedOut ? 'bg-amber-500/20 border-amber-500/40 text-amber-200' : 'bg-rose-500/20 border-rose-500/40 text-rose-300' ?> border text-xs font-semibold flex items-start gap-2.5 animate-fade-in">
                <span class="material-symbols-outlined text-lg shrink-0 mt-0.5"><?= $isLockedOut ? 'lock_clock' : 'error' ?></span>
                <div class="flex-1 leading-relaxed"><?= strip_tags($error, '<strong><b>') ?></div>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/admin/login.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Username or Email</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3.5 top-3 text-slate-400 text-lg">person</span>
                    <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? 'admin') ?>" required <?= $isLockedOut ? 'disabled' : '' ?> placeholder="Enter username or email" class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-700/50 border border-slate-600/60 text-sm text-white focus:outline-none focus:border-primary disabled:opacity-50 disabled:cursor-not-allowed"/>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Password</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3.5 top-3 text-slate-400 text-lg">lock</span>
                    <input type="password" name="password" required <?= $isLockedOut ? 'disabled' : '' ?> placeholder="••••••••" class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-700/50 border border-slate-600/60 text-sm text-white focus:outline-none focus:border-primary disabled:opacity-50 disabled:cursor-not-allowed"/>
                </div>
                <div class="flex items-center justify-between mt-2 text-[11px] text-slate-400">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px] text-primary">security</span>
                        <span>Rate Limiting: Max 5 attempts</span>
                    </span>
                    <span>15 min lockout</span>
                </div>
            </div>

            <button type="submit" <?= $isLockedOut ? 'disabled' : '' ?> class="w-full py-3.5 rounded-xl bg-primary hover:bg-primary-container text-white font-bold text-sm shadow-lg shadow-primary/30 transition-all active:scale-95 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none mt-2">
                <span class="material-symbols-outlined text-lg"><?= $isLockedOut ? 'lock' : 'login' ?></span>
                <span><?= $isLockedOut ? 'Temporarily Locked' : 'Sign In to Dashboard' ?></span>
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-700/60 text-center">
            <a href="<?= BASE_URL ?>/index.php" class="text-xs text-slate-400 hover:text-slate-200 flex items-center justify-center gap-1">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Back to <?= APP_NAME ?> Website</span>
            </a>
        </div>
    </div>
</body>
</html>

