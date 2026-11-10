<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_admin_logged_in()) {
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $db = get_db();
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_user_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_full_name'] = $admin['full_name'];
            $_SESSION['admin_role'] = $admin['role'];

            set_flash('success', "Welcome back, " . htmlspecialchars($admin['full_name']) . "!");
            header('Location: ' . BASE_URL . '/admin/index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
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
            <div class="mb-5 p-3.5 rounded-xl bg-rose-500/20 border border-rose-500/40 text-rose-300 text-xs font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-base">error</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/admin/login.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Username or Email</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3.5 top-3 text-slate-400 text-lg">person</span>
                    <input type="text" name="username" value="admin" required class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-700/50 border border-slate-600/60 text-sm text-white focus:outline-none focus:border-primary"/>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Password</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3.5 top-3 text-slate-400 text-lg">lock</span>
                    <input type="password" name="password" value="admin123" required class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-700/50 border border-slate-600/60 text-sm text-white focus:outline-none focus:border-primary"/>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Default demo: <code>admin</code> / <code>admin123</code></p>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-primary hover:bg-primary-container text-white font-bold text-sm shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2 mt-2">
                <span class="material-symbols-outlined text-lg">login</span>
                <span>Sign In to Dashboard</span>
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

