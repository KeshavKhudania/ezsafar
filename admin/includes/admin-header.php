<?php
require_once __DIR__ . '/../auth-check.php';
$adminCurrentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= isset($adminTitle) ? htmlspecialchars($adminTitle) . ' | ' . APP_NAME . ' Admin' : APP_NAME . ' Admin Console' ?></title>
    
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
                        'secondary-container': '#fe6b00',
                        surface: '#f8fafc'
                    },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }
                }
            }
        };
    </script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">

<div class="min-h-screen flex flex-col md:flex-row">
    <!-- Admin Sidebar -->
    <aside class="w-full md:w-64 bg-slate-900 text-slate-200 flex-shrink-0 flex flex-col justify-between">
        <div>
            <!-- Admin Brand -->
            <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                <a href="<?= BASE_URL ?>/admin/index.php" class="flex items-center gap-3">
                    <img src="<?= BASE_URL ?>/assets/images/logo-card.png" alt="EzSafar Admin" class="w-10 h-10 rounded-xl object-contain shadow-md shrink-0"/>
                    <div>
                        <div class="font-black text-white text-lg tracking-tight">EZ<span class="text-orange-400">Safar</span></div>
                        <div class="text-[10px] text-slate-400 uppercase tracking-widest font-semibold">Admin Hub • Jind</div>
                    </div>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5 text-sm font-semibold">
                <a href="<?= BASE_URL ?>/admin/index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($adminCurrentPage === 'index.php') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
                    <span class="material-symbols-outlined text-[20px]">dashboard</span>
                    <span>Dashboard</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/bookings.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($adminCurrentPage === 'bookings.php') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
                    <span class="material-symbols-outlined text-[20px]">book_online</span>
                    <span>Rental Bookings</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/rental-cars.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($adminCurrentPage === 'rental-cars.php') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
                    <span class="material-symbols-outlined text-[20px]">directions_car</span>
                    <span>Rental Fleet</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/used-cars.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($adminCurrentPage === 'used-cars.php') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
                    <span class="material-symbols-outlined text-[20px]">car_tag</span>
                    <span>Certified Used Cars</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/sell-requests.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($adminCurrentPage === 'sell-requests.php') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
                    <span class="material-symbols-outlined text-[20px]">payments</span>
                    <span>Sell Car Leads</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/inquiries.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($adminCurrentPage === 'inquiries.php') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
                    <span class="material-symbols-outlined text-[20px]">contact_support</span>
                    <span>Inquiries &amp; Test Drives</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/blogs.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= (in_array($adminCurrentPage, ['blogs.php', 'blog-edit.php'])) ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
                    <span class="material-symbols-outlined text-[20px]">article</span>
                    <span>Blog Articles</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/settings.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($adminCurrentPage === 'settings.php') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
                    <span class="material-symbols-outlined text-[20px]">settings</span>
                    <span>Website &amp; SEO Settings</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/admin-users.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($adminCurrentPage === 'admin-users.php') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
                    <span class="material-symbols-outlined text-[20px]">manage_accounts</span>
                    <span>Admin Team</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar Bottom / User -->
        <div class="p-4 border-t border-slate-800 space-y-3">
            <div class="flex items-center gap-3 px-3 py-2 rounded-xl bg-slate-800/60">
                <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-slate-300 font-bold text-xs">
                    <?= strtoupper(substr($_SESSION['admin_full_name'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="overflow-hidden">
                    <div class="text-xs font-bold text-white truncate"><?= htmlspecialchars($_SESSION['admin_full_name'] ?? 'Admin') ?></div>
                    <div class="text-[10px] text-slate-400 uppercase"><?= htmlspecialchars($_SESSION['admin_role'] ?? 'Staff') ?></div>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs font-semibold px-2">
                <a href="<?= BASE_URL ?>/index.php" target="_blank" class="text-slate-400 hover:text-white flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                    <span>View Site</span>
                </a>
                <a href="<?= BASE_URL ?>/admin/logout.php" class="text-rose-400 hover:text-rose-300 flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">logout</span>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Top Bar -->
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between shadow-sm sticky top-0 z-30">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-black text-slate-800"><?= $adminTitle ?? 'Operations Console' ?></h2>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>MySQL Database: Active</span>
                </span>
            </div>
        </header>

        <!-- Flash Notice in Admin -->
        <div class="p-6 pb-0">
            <?= render_flash() ?>
        </div>

        <div class="p-6 flex-1">

