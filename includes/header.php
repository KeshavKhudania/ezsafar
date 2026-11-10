<?php
require_once __DIR__ . '/functions.php';
$currentPage = basename($_SERVER['PHP_SELF']);

// Load Dynamic Settings from Database
$siteName = get_setting('site_name', APP_NAME);
$siteTagline = get_setting('site_tagline', APP_TAGLINE);

if (isset($pageTitle) && !empty($pageTitle)) {
    $metaTitle = (stripos($pageTitle, $siteName) !== false) ? $pageTitle : ($pageTitle . ' | ' . $siteName);
} else {
    $metaTitle = get_setting('meta_title_default', $siteName . ' - Premium Car Rentals, Buy & Sell Pre-Owned Cars in Jind');
}

$metaDesc = isset($pageDesc) ? $pageDesc : get_setting('meta_description_default', 'Haryana\'s premier self-drive car rental and certified used car trading platform in Jind. Zero security deposit, 140-point inspection, and instant payment for car sellers.');
$metaKeywords = isset($pageKeywords) ? $pageKeywords : get_setting('meta_keywords', 'car rental jind, self drive cars haryana, used cars in jind, buy second hand car jind');
$metaAuthor = get_setting('meta_author', 'EzSafar Mobility Solutions Pvt. Ltd.');
$metaRobots = get_setting('meta_robots', 'index, follow');
$headerOgImage = !empty($ogImage) ? $ogImage : get_setting('og_image_url', '');
$googleAnalyticsId = get_setting('google_analytics_id', '');
$customHeadScripts = get_setting('custom_head_scripts', '');

if (!isset($canonicalUrl)) {
    $canonicalUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
}

$primaryCity = get_setting('primary_city', HUB_PRIMARY_CITY);
$primaryState = get_setting('primary_state', HUB_PRIMARY_STATE);
$phoneSupport = get_setting('contact_phone', PHONE_SUPPORT);
$phoneWhatsapp = get_setting('contact_whatsapp', PHONE_WHATSAPP);
?>
<!DOCTYPE html>
<html class="scroll-smooth" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    
    <!-- Primary SEO Meta Tags -->
    <title><?= htmlspecialchars($metaTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc) ?>"/>
    <meta name="keywords" content="<?= htmlspecialchars($metaKeywords) ?>"/>
    <meta name="author" content="<?= htmlspecialchars($metaAuthor) ?>"/>
    <meta name="robots" content="<?= htmlspecialchars($metaRobots) ?>"/>
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>"/>

    <!-- Favicon & Touch Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/images/favicon.png"/>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/favicon.ico"/>
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>/assets/images/apple-touch-icon.png"/>

    <!-- Open Graph / Facebook / WhatsApp Preview -->
    <meta property="og:type" content="website"/>
    <meta property="og:title" content="<?= htmlspecialchars($metaTitle) ?>"/>
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>"/>
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>"/>
    <?php if (!empty($headerOgImage)): ?>
        <meta property="og:image" content="<?= htmlspecialchars($headerOgImage) ?>"/>
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image"/>
    <meta name="twitter:title" content="<?= htmlspecialchars($metaTitle) ?>"/>
    <meta name="twitter:description" content="<?= htmlspecialchars($metaDesc) ?>"/>
    <?php if (!empty($headerOgImage)): ?>
        <meta name="twitter:image" content="<?= htmlspecialchars($headerOgImage) ?>"/>
    <?php endif; ?>

    <!-- Google Analytics Tracking (if configured in Settings) -->
    <?php if (!empty($googleAnalyticsId)): ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($googleAnalyticsId) ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?= htmlspecialchars($googleAnalyticsId) ?>');
        </script>
    <?php endif; ?>

    <!-- Custom Head Injections from Settings -->
    <?php if (!empty($customHeadScripts)): ?>
        <?= $customHeadScripts ?>
    <?php endif; ?>
    
    <!-- Google Fonts & Material Icons -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <style>
        @layer base { 
            html, body { margin: 0; padding: 0; } 
            body { overscroll-behavior: none; } 
            main > :first-child { margin-top: 0 !important; } 
            main > :last-child { margin-bottom: 0 !important; } 
        } 
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
    
    <!-- Tailwind CSS with Stitch EzSafar Design Tokens -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "secondary-fixed": "#ffdbcc",
                        "surface-tint": "#0057ce",
                        "surface-container-low": "#f2f3ff",
                        "surface-container": "#eaedff",
                        "on-primary-fixed": "#001946",
                        "surface-container-high": "#e2e7ff",
                        "on-error": "#ffffff",
                        "inverse-surface": "#283044",
                        "on-tertiary": "#ffffff",
                        "surface-variant": "#dae2fd",
                        "primary": "#004cb6",
                        "primary-container": "#0b63e5",
                        "on-error-container": "#93000a",
                        "surface-container-lowest": "#ffffff",
                        "on-secondary-fixed": "#351000",
                        "tertiary-fixed-dim": "#4edea3",
                        "on-primary-container": "#e8ecff",
                        "surface-bright": "#faf8ff",
                        "on-surface-variant": "#424654",
                        "surface": "#faf8ff",
                        "primary-fixed": "#dae2ff",
                        "secondary-container": "#fe6b00",
                        "tertiary-fixed": "#6ffbbe",
                        "on-secondary-container": "#572000",
                        "on-tertiary-fixed": "#002113",
                        "on-secondary-fixed-variant": "#7a3000",
                        "secondary": "#a04100",
                        "on-background": "#131b2e",
                        "on-surface": "#131b2e",
                        "on-tertiary-container": "#a3ffd1",
                        "surface-dim": "#d2d9f4",
                        "outline": "#727786",
                        "secondary-fixed-dim": "#ffb693",
                        "tertiary": "#005f40",
                        "primary-fixed-dim": "#b1c5ff",
                        "surface-container-highest": "#dae2fd",
                        "on-tertiary-fixed-variant": "#005236",
                        "error": "#ba1a1a",
                        "inverse-on-surface": "#eef0ff",
                        "on-primary": "#ffffff",
                        "background": "#faf8ff",
                        "outline-variant": "#c2c6d7",
                        "error-container": "#ffdad6",
                        "on-primary-fixed-variant": "#00419e",
                        "inverse-primary": "#b1c5ff",
                        "tertiary-container": "#007a53",
                        "on-secondary": "#ffffff"
                    },
                    fontFamily: {
                        sans: ["Plus Jakarta Sans", "sans-serif"]
                    }
                }
            }
        };
    </script>
</head>
<body class="bg-surface font-sans text-on-surface antialiased selection:bg-primary-fixed selection:text-on-primary-fixed">

<!-- Top Utility Banner -->
<header class="fixed top-0 left-0 right-0 w-full z-50 shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
    <div class="w-full bg-surface-container-high text-on-surface-variant text-xs py-1.5 px-4 md:px-8 border-b-0">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-tertiary animate-pulse"></span>
                    <span>Current Hub: <strong class="text-on-surface font-semibold"><?= htmlspecialchars($primaryCity) ?>, <?= htmlspecialchars($primaryState) ?></strong></span>
                    <span class="hidden sm:inline text-outline-variant">•</span>
                    <span class="hidden sm:inline">Self-Drive &amp; 140-Pt Certified Cars</span>
                </div>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <a href="tel:<?= htmlspecialchars($phoneSupport) ?>" class="hover:text-primary transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px] text-primary">phone</span>
                    <span class="hidden md:inline"><?= htmlspecialchars($phoneSupport) ?></span>
                </a>
                <span class="text-outline-variant">•</span>
                <a href="https://wa.me/<?= htmlspecialchars($phoneWhatsapp) ?>" target="_blank" rel="noopener" class="hover:text-tertiary transition-colors flex items-center gap-1 text-tertiary font-semibold">
                    <span class="material-symbols-outlined text-[14px]">chat</span>
                    <span>WhatsApp Desk</span>
                </a>
                <?php if (is_admin_logged_in()): ?>
                    <span class="text-outline-variant">•</span>
                    <a href="<?= BASE_URL ?>/admin/index.php" class="text-primary font-semibold hover:underline flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">dashboard</span> Admin
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <div class="w-full bg-surface-container-lowest/95 backdrop-blur-md border-b border-outline-variant/15">
        <div class="max-w-7xl mx-auto px-4 md:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <div class="flex items-center gap-8">
                <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-3 group">
                    <img src="<?= BASE_URL ?>/assets/images/logo-card.png" alt="EzSafar Logo" class="w-12 h-12 rounded-2xl object-contain shadow-sm border border-outline-variant/15 group-hover:scale-105 transition-transform shrink-0"/>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-2xl font-black tracking-tight text-on-surface">EZ<span class="text-secondary-container">Safar</span></span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-primary-fixed text-on-primary-fixed">JIND</span>
                        </div>
                        <span class="text-[11px] font-medium text-on-surface-variant block -mt-0.5">Automotive Mobility</span>
                    </div>
                </a>

                <!-- Desktop Nav Links -->
                <nav class="hidden lg:flex items-center gap-1">
                    <a href="<?= BASE_URL ?>/index.php" class="px-3.5 py-2 font-semibold text-sm rounded-lg transition-all <?= ($currentPage === 'index.php') ? 'text-primary bg-surface-container' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' ?>">
                        Home
                    </a>
                    <a href="<?= BASE_URL ?>/car-rentals.php" class="px-3.5 py-2 font-semibold text-sm rounded-lg transition-all <?= ($currentPage === 'car-rentals.php') ? 'text-primary bg-surface-container' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' ?>">
                        Car Rentals
                    </a>
                    <a href="<?= BASE_URL ?>/buy-cars.php" class="px-3.5 py-2 font-semibold text-sm rounded-lg transition-all <?= ($currentPage === 'buy-cars.php') ? 'text-primary bg-surface-container' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' ?>">
                        Buy Used Cars
                    </a>
                    <a href="<?= BASE_URL ?>/sell-your-car.php" class="px-3.5 py-2 font-semibold text-sm rounded-lg transition-all <?= ($currentPage === 'sell-your-car.php') ? 'text-primary bg-surface-container' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' ?>">
                        Sell Your Car
                    </a>
                    <a href="<?= BASE_URL ?>/blog.php" class="px-3.5 py-2 font-semibold text-sm rounded-lg transition-all <?= ($currentPage === 'blog.php' || $currentPage === 'blog-post.php') ? 'text-primary bg-surface-container' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' ?>">
                        Blog &amp; Guides
                    </a>
                </nav>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3">
                <a href="<?= BASE_URL ?>/sell-your-car.php" class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-secondary-container hover:bg-secondary text-on-secondary font-semibold text-sm shadow-md transition-all active:scale-95">
                    <span class="material-symbols-outlined text-[18px]">currency_rupee</span>
                    <span>Instant Car Valuation</span>
                </a>

                <?php if (is_admin_logged_in()): ?>
                    <a href="<?= BASE_URL ?>/admin/index.php" class="hidden md:inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-surface-container-high hover:bg-surface-container-highest text-on-surface font-semibold text-sm transition-colors">
                        <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                        <span>Dashboard</span>
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/admin/login.php" class="hidden md:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-surface-container-high hover:bg-surface-container-highest text-on-surface-variant text-xs font-semibold transition-colors" title="Staff & Admin Portal">
                        <span class="material-symbols-outlined text-[16px]">lock</span>
                        <span>Admin</span>
                    </a>
                <?php endif; ?>

                <!-- Mobile Menu Button -->
                <button type="button" id="mobile-menu-btn" onclick="document.getElementById('mobile-drawer').classList.toggle('hidden')" class="lg:hidden p-2 rounded-xl text-on-surface-variant hover:bg-surface-container hover:text-on-surface">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div id="mobile-drawer" class="hidden lg:hidden border-t border-outline-variant/15 bg-surface-container-lowest px-4 py-4 space-y-2">
            <a href="<?= BASE_URL ?>/index.php" class="block px-3 py-2.5 rounded-lg text-sm font-semibold <?= ($currentPage === 'index.php') ? 'text-primary bg-surface-container' : 'text-on-surface hover:bg-surface-container' ?>">Home</a>
            <a href="<?= BASE_URL ?>/car-rentals.php" class="block px-3 py-2.5 rounded-lg text-sm font-semibold <?= ($currentPage === 'car-rentals.php') ? 'text-primary bg-surface-container' : 'text-on-surface hover:bg-surface-container' ?>">Car Rentals (Self-Drive &amp; Chauffeur)</a>
            <a href="<?= BASE_URL ?>/buy-cars.php" class="block px-3 py-2.5 rounded-lg text-sm font-semibold <?= ($currentPage === 'buy-cars.php') ? 'text-primary bg-surface-container' : 'text-on-surface hover:bg-surface-container' ?>">Buy Certified Used Cars</a>
            <a href="<?= BASE_URL ?>/sell-your-car.php" class="block px-3 py-2.5 rounded-lg text-sm font-semibold <?= ($currentPage === 'sell-your-car.php') ? 'text-primary bg-surface-container' : 'text-on-surface hover:bg-surface-container' ?>">Sell Your Car (Instant Payment)</a>
            <a href="<?= BASE_URL ?>/blog.php" class="block px-3 py-2.5 rounded-lg text-sm font-semibold <?= ($currentPage === 'blog.php' || $currentPage === 'blog-post.php') ? 'text-primary bg-surface-container' : 'text-on-surface hover:bg-surface-container' ?>">Blog &amp; Knowledge Hub</a>
            <div class="pt-3 border-t border-outline-variant/10 flex flex-col gap-2">
                <a href="tel:<?= PHONE_SUPPORT ?>" class="flex items-center gap-2 text-sm text-primary font-semibold px-3 py-2 rounded-lg bg-surface-container">
                    <span class="material-symbols-outlined">call</span> Call Jind Hub: <?= PHONE_SUPPORT ?>
                </a>
                <a href="<?= BASE_URL ?>/admin/login.php" class="flex items-center gap-2 text-sm text-on-surface-variant px-3 py-2 rounded-lg hover:bg-surface-container">
                    <span class="material-symbols-outlined">admin_panel_settings</span> Staff / Admin Login
                </a>
            </div>
        </div>
    </div>
</header>

<!-- Main Container with Top Padding for Fixed Nav -->
<main class="w-full pt-28 bg-surface min-h-[calc(100vh-420px)]">
    <?= render_flash() ?>

