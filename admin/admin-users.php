<?php
require_once __DIR__ . '/auth-check.php';
$db = get_db();

$currentAdminId = (int)($_SESSION['admin_user_id'] ?? 0);

// Handle Actions (Process POST before admin-header.php HTML output)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'add') {
        $fullName = sanitize($_POST['full_name'] ?? '');
        $username = strtolower(trim(preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $_POST['username'] ?? '')));
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $phone = sanitize($_POST['phone'] ?? '');
        $role = sanitize($_POST['role'] ?? 'Admin');
        $password = $_POST['password'] ?? '';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($fullName) || empty($username) || !$email || empty($password)) {
            set_flash('error', "Full name, valid email, username, and password are required.");
        } elseif (strlen($password) < 6) {
            set_flash('error', "Password must be at least 6 characters long.");
        } else {
            // Check uniqueness of username and email
            $stmt = $db->prepare("SELECT COUNT(*) FROM admins WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                set_flash('error', "An administrator with this username or email already exists.");
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO admins (username, email, password_hash, full_name, role, phone, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hash, $fullName, $role, $phone, $isActive]);
                set_flash('success', "Administrator account for '$fullName' created successfully.");
            }
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $fullName = sanitize($_POST['full_name'] ?? '');
        $username = strtolower(trim(preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $_POST['username'] ?? '')));
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $phone = sanitize($_POST['phone'] ?? '');
        $role = sanitize($_POST['role'] ?? 'Admin');
        $password = $_POST['password'] ?? '';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($id <= 0 || empty($fullName) || empty($username) || !$email) {
            set_flash('error', "Required administrator fields are missing or invalid.");
        } else {
            // Check uniqueness excluding current ID
            $stmt = $db->prepare("SELECT COUNT(*) FROM admins WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $id]);
            if ($stmt->fetchColumn() > 0) {
                set_flash('error', "Another administrator with this username or email already exists.");
            } else {
                // If this is the current logged-in user, ensure they remain active
                if ($id === $currentAdminId) {
                    $isActive = 1;
                }

                // If password provided, update hash; else keep existing
                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        set_flash('error', "New password must be at least 6 characters long.");
                        header('Location: ' . BASE_URL . '/admin/admin-users.php');
                        exit;
                    }
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE admins SET full_name=?, username=?, email=?, phone=?, role=?, is_active=?, password_hash=? WHERE id=?");
                    $stmt->execute([$fullName, $username, $email, $phone, $role, $isActive, $hash, $id]);
                } else {
                    $stmt = $db->prepare("UPDATE admins SET full_name=?, username=?, email=?, phone=?, role=?, is_active=? WHERE id=?");
                    $stmt->execute([$fullName, $username, $email, $phone, $role, $isActive, $id]);
                }

                // If updating current user's profile, update session
                if ($id === $currentAdminId) {
                    $_SESSION['admin_full_name'] = $fullName;
                    $_SESSION['admin_username'] = $username;
                    $_SESSION['admin_role'] = $role;
                }

                set_flash('success', "Administrator account for '$fullName' updated successfully.");
            }
        }
    } elseif ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        $current = (int)($_POST['current_status'] ?? 0);

        if ($id === $currentAdminId) {
            set_flash('error', "You cannot deactivate your own active session account.");
        } else {
            $new = $current ? 0 : 1;
            $stmt = $db->prepare("UPDATE admins SET is_active = ? WHERE id = ?");
            $stmt->execute([$new, $id]);
            set_flash('info', "Administrator account status updated.");
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);

        if ($id === $currentAdminId) {
            set_flash('error', "You cannot delete your own active administrator account.");
        } else {
            // Check if this is the only Super Admin
            $superCount = (int)$db->query("SELECT COUNT(*) FROM admins WHERE role = 'Super Admin'")->fetchColumn();
            $targetRole = $db->query("SELECT role FROM admins WHERE id = $id")->fetchColumn();

            if ($targetRole === 'Super Admin' && $superCount <= 1) {
                set_flash('error', "Cannot delete the sole Super Administrator. Please assign another Super Admin first.");
            } else {
                $stmt = $db->prepare("DELETE FROM admins WHERE id = ?");
                $stmt->execute([$id]);
                set_flash('warning', "Administrator account deleted permanently.");
            }
        }
    }

    header('Location: ' . BASE_URL . '/admin/admin-users.php');
    exit;
}

$adminTitle = "Admin Team & Staff Management";
require_once __DIR__ . '/includes/admin-header.php';

$admins = $db->query("SELECT * FROM admins ORDER BY id ASC")->fetchAll();
?>

<!-- Header & Add Button -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-xl font-bold text-slate-800">Admin Team Management (<?= count($admins) ?> Users)</h3>
        <p class="text-xs text-slate-500">Create, edit, and assign roles for administrators and staff members.</p>
    </div>
    <button onclick="document.getElementById('add-admin-modal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold shadow flex items-center gap-1.5 self-start">
        <span class="material-symbols-outlined text-base">person_add</span>
        <span>Create New Administrator</span>
    </button>
</div>

<!-- Security Assurance Banner -->
<div class="mb-6 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-950 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
    <div class="flex items-center gap-2.5">
        <span class="material-symbols-outlined text-blue-600 text-xl shrink-0">shield</span>
        <div>
            <span class="font-bold">Brute-Force Rate Limiting Active:</span>
            <span class="text-blue-900">All administrator logins are restricted to a maximum of 5 failed attempts per 15-minute window.</span>
        </div>
    </div>
    <span class="px-2.5 py-1 rounded-full bg-blue-200/80 text-blue-900 font-bold uppercase text-[10px] shrink-0">
        Active Protection
    </span>
</div>

<!-- Admins Table -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-200">
                <tr>
                    <th class="p-4">Administrator</th>
                    <th class="p-4">Contact Info</th>
                    <th class="p-4">Role &amp; Permissions</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Last Login</th>
                    <th class="p-4">Created Date</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($admins as $a): 
                    $isSelf = ($a['id'] === $currentAdminId);
                ?>
                    <tr class="hover:bg-slate-50/60 transition-colors <?= $isSelf ? 'bg-blue-50/20' : '' ?>">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-primary to-primary-container text-white flex items-center justify-center font-extrabold text-sm shadow shrink-0">
                                    <?= strtoupper(substr($a['full_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 text-sm flex items-center gap-1.5">
                                        <span><?= htmlspecialchars($a['full_name']) ?></span>
                                        <?php if ($isSelf): ?>
                                            <span class="px-2 py-0.2 rounded-full bg-blue-100 text-blue-800 text-[10px] font-bold">You</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-mono">@<?= htmlspecialchars($a['username']) ?> • ID #<?= $a['id'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="font-medium text-slate-700"><?= htmlspecialchars($a['email']) ?></div>
                            <div class="text-[11px] text-slate-400"><?= htmlspecialchars($a['phone'] ?: 'No phone added') ?></div>
                        </td>
                        <td class="p-4">
                            <?php
                            $roleColors = [
                                'Super Admin' => 'bg-purple-100 text-purple-800 border-purple-200',
                                'Admin'       => 'bg-blue-100 text-blue-800 border-blue-200',
                                'Manager'     => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'Staff'       => 'bg-slate-100 text-slate-700 border-slate-200'
                            ];
                            $badge = $roleColors[$a['role']] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                            ?>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border <?= $badge ?>">
                                <?= htmlspecialchars($a['role']) ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <?php if ($isSelf): ?>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    ● Active
                                </span>
                            <?php else: ?>
                                <form action="" method="POST" class="inline">
                                    <input type="hidden" name="action" value="toggle_status"/>
                                    <input type="hidden" name="id" value="<?= $a['id'] ?>"/>
                                    <input type="hidden" name="current_status" value="<?= $a['is_active'] ?>"/>
                                    <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold cursor-pointer transition-all <?= $a['is_active'] ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200' ?>">
                                        <?= $a['is_active'] ? '● Active' : '○ Deactivated' ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-slate-500 font-medium">
                            <?= !empty($a['last_login']) ? date('M d, Y h:i A', strtotime($a['last_login'])) : '<span class="text-slate-400 italic">Never</span>' ?>
                        </td>
                        <td class="p-4 text-slate-500 font-medium">
                            <?= date('M d, Y', strtotime($a['created_at'])) ?>
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" onclick="openEditAdminModal(<?= htmlspecialchars(json_encode($a)) ?>)" class="p-1.5 rounded-lg text-slate-500 hover:text-primary hover:bg-slate-100" title="Edit Admin Details">
                                    <span class="material-symbols-outlined text-base">edit</span>
                                </button>
                                <?php if (!$isSelf): ?>
                                    <form action="" method="POST" onsubmit="return confirm('Permanently delete administrator account for <?= addslashes($a['full_name']) ?>?')" class="inline">
                                        <input type="hidden" name="action" value="delete"/>
                                        <input type="hidden" name="id" value="<?= $a['id'] ?>"/>
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Delete Admin">
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- EDIT ADMIN MODAL -->
<div id="edit-admin-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 shadow-2xl border border-slate-200 animate-fade-in">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-bold uppercase tracking-wider">Account Editor</span>
                <h3 class="text-lg font-bold text-slate-800 mt-1" id="edit-modal-title">Edit Administrator</h3>
            </div>
            <button onclick="document.getElementById('edit-admin-modal').classList.add('hidden')" class="p-1 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="" method="POST" class="mt-5 space-y-4 text-xs">
            <input type="hidden" name="action" value="edit"/>
            <input type="hidden" name="id" id="edit-admin-id" value=""/>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Full Name *</label>
                <input type="text" name="full_name" id="edit-admin-name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Username *</label>
                    <input type="text" name="username" id="edit-admin-username" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono text-slate-800 focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Role</label>
                    <select name="role" id="edit-admin-role" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none">
                        <option value="Super Admin">Super Admin</option>
                        <option value="Admin">Admin</option>
                        <option value="Manager">Manager</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Email Address *</label>
                    <input type="email" name="email" id="edit-admin-email" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Phone Number</label>
                    <input type="text" name="phone" id="edit-admin-phone" placeholder="+91 9876543210" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none"/>
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                <label class="block font-bold text-slate-700 uppercase text-[11px]">Change Password (Optional)</label>
                <input type="password" name="password" placeholder="Leave empty to keep existing password" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
                <p class="text-[10px] text-slate-400">Enter at least 6 characters only if you want to reset their credentials.</p>
            </div>

            <div class="flex items-center gap-2 p-3 rounded-xl bg-slate-50 border border-slate-200">
                <input type="checkbox" name="is_active" id="edit-admin-active" value="1" class="w-4 h-4 text-primary rounded"/>
                <label for="edit-admin-active" class="font-bold text-slate-700 cursor-pointer">Account Active &amp; Allowed to Log In</label>
            </div>

            <div class="pt-3 flex justify-end gap-3 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('edit-admin-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-bold hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white font-bold shadow flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">save</span>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ADD ADMIN MODAL -->
<div id="add-admin-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 shadow-2xl border border-slate-200 animate-fade-in">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <h3 class="text-lg font-bold text-slate-800">Add New Administrator</h3>
            <button onclick="document.getElementById('add-admin-modal').classList.add('hidden')" class="p-1 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="" method="POST" class="mt-5 space-y-4 text-xs">
            <input type="hidden" name="action" value="add"/>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Full Name *</label>
                <input type="text" name="full_name" required placeholder="e.g. Ramesh Kumar" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Username *</label>
                    <input type="text" name="username" required placeholder="e.g. ramesh_jind" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono text-slate-800 focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Role</label>
                    <select name="role" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none">
                        <option value="Admin" selected>Admin</option>
                        <option value="Super Admin">Super Admin</option>
                        <option value="Manager">Manager</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Email Address *</label>
                    <input type="email" name="email" required placeholder="e.g. ramesh@ezsafar.in" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Phone Number</label>
                    <input type="text" name="phone" placeholder="+91 9812345678" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none"/>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Login Password *</label>
                <input type="password" name="password" required placeholder="Minimum 6 characters" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
            </div>

            <div class="flex items-center gap-2 p-3 rounded-xl bg-slate-50 border border-slate-200">
                <input type="checkbox" name="is_active" id="add-admin-active" value="1" checked class="w-4 h-4 text-primary rounded"/>
                <label for="add-admin-active" class="font-bold text-slate-700 cursor-pointer">Account Active &amp; Allowed to Log In</label>
            </div>

            <div class="pt-3 flex justify-end gap-3 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('add-admin-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-bold hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white font-bold shadow flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">person_add</span>
                    <span>Create Account</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditAdminModal(admin) {
    document.getElementById('edit-admin-id').value = admin.id;
    document.getElementById('edit-modal-title').innerText = 'Edit ' + admin.full_name;
    document.getElementById('edit-admin-name').value = admin.full_name;
    document.getElementById('edit-admin-username').value = admin.username;
    document.getElementById('edit-admin-email').value = admin.email;
    document.getElementById('edit-admin-phone').value = admin.phone || '';
    document.getElementById('edit-admin-role').value = admin.role || 'Admin';
    document.getElementById('edit-admin-active').checked = (parseInt(admin.is_active) === 1);

    document.getElementById('edit-admin-modal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
