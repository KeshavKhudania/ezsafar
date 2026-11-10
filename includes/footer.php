</main>

<?php
$fHubAddress = get_setting('primary_hub_address', HUB_LOCATION);
$fPhone = get_setting('contact_phone', PHONE_SUPPORT);
$fEmail = get_setting('contact_email', EMAIL_SUPPORT);
$fWhatsapp = get_setting('contact_whatsapp', PHONE_WHATSAPP);
$fSiteName = get_setting('site_name', APP_NAME);
$fTagline = get_setting('site_tagline', APP_TAGLINE);
$fCity = get_setting('primary_city', HUB_PRIMARY_CITY);
?>

<!-- Footer -->
<footer class="w-full bg-surface-container-high border-t border-outline-variant/15 pt-16 pb-12 mt-16 text-on-surface-variant">
    <div class="max-w-7xl mx-auto px-4 md:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 pb-12 border-b border-outline-variant/15">
            <!-- Brand Column -->
            <div class="lg:col-span-2 flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-primary to-primary-container flex items-center justify-center text-white shadow-md">
                        <span class="material-symbols-outlined text-2xl">directions_car</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-2xl font-extrabold text-on-surface"><?= htmlspecialchars($fSiteName) ?></span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-primary-fixed text-on-primary-fixed"><?= htmlspecialchars($fCity) ?></span>
                        </div>
                        <span class="text-xs text-on-surface-variant"><?= htmlspecialchars($fTagline) ?></span>
                    </div>
                </div>
                <p class="text-sm leading-relaxed max-w-sm">
                    Haryana's highest-rated automotive mobility platform. We provide reliable self-drive car rentals with zero security deposits and certified 140-point inspected used cars with warranty and free RC transfer.
                </p>
                <div class="flex flex-col gap-2 text-sm mt-2">
                    <div class="flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-primary text-[20px] shrink-0 mt-0.5">location_on</span>
                        <span><?= htmlspecialchars($fHubAddress) ?></span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-primary text-[20px] shrink-0">phone</span>
                        <a href="tel:<?= htmlspecialchars($fPhone) ?>" class="hover:text-primary font-medium"><?= htmlspecialchars($fPhone) ?></a>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-primary text-[20px] shrink-0">mail</span>
                        <a href="mailto:<?= htmlspecialchars($fEmail) ?>" class="hover:text-primary"><?= htmlspecialchars($fEmail) ?></a>
                    </div>
                </div>
            </div>

            <!-- Quick Links: Rentals -->
            <div class="flex flex-col gap-3">
                <h4 class="font-bold text-sm text-on-surface tracking-wide uppercase">Car Rentals</h4>
                <ul class="flex flex-col gap-2 text-sm">
                    <li><a href="<?= BASE_URL ?>/car-rentals.php?cat=Hatchback" class="hover:text-primary">Self-Drive Hatchbacks</a></li>
                    <li><a href="<?= BASE_URL ?>/car-rentals.php?cat=Compact+SUV" class="hover:text-primary">SUV &amp; Thar 4x4 Fleet</a></li>
                    <li><a href="<?= BASE_URL ?>/car-rentals.php?cat=Sedan" class="hover:text-primary">Executive Sedans (City/Ciaz)</a></li>
                    <li><a href="<?= BASE_URL ?>/car-rentals.php?cat=MUV+%2F+7-Seater" class="hover:text-primary">Innova Crysta 7-Seater</a></li>
                    <li><a href="<?= BASE_URL ?>/car-rentals.php" class="hover:text-primary">Wedding Luxury Cars</a></li>
                    <li><a href="<?= BASE_URL ?>/car-rentals.php" class="hover:text-primary">Delhi / Chandigarh Airport Drop</a></li>
                </ul>
            </div>

            <!-- Quick Links: Used Cars -->
            <div class="flex flex-col gap-3">
                <h4 class="font-bold text-sm text-on-surface tracking-wide uppercase">Buy &amp; Sell Cars</h4>
                <ul class="flex flex-col gap-2 text-sm">
                    <li><a href="<?= BASE_URL ?>/buy-cars.php" class="hover:text-primary">Certified Used Cars</a></li>
                    <li><a href="<?= BASE_URL ?>/buy-cars.php" class="hover:text-primary">140-Point Inspection Matrix</a></li>
                    <li><a href="<?= BASE_URL ?>/sell-your-car.php" class="hover:text-primary">Instant Car Valuation</a></li>
                    <li><a href="<?= BASE_URL ?>/sell-your-car.php" class="hover:text-primary">Doorstep Inspection in Jind</a></li>
                    <li><a href="<?= BASE_URL ?>/buy-cars.php" class="hover:text-primary">Easy Low-EMI Financing</a></li>
                    <li><a href="<?= BASE_URL ?>/buy-cars.php" class="hover:text-primary">Free Haryana RC Transfer</a></li>
                    <li><a href="<?= BASE_URL ?>/blog.php" class="hover:text-primary font-bold text-primary">Knowledge Hub &amp; Guides</a></li>
                </ul>
            </div>

            <!-- Regional Hubs -->
            <div class="flex flex-col gap-3">
                <h4 class="font-bold text-sm text-on-surface tracking-wide uppercase">Regional Hubs</h4>
                <ul class="flex flex-col gap-2 text-sm">
                    <li><span class="text-on-surface font-semibold flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Jind Central (Railway Rd)</span></li>
                    <li><span class="hover:text-on-surface">Safidon Road Depot, Jind</span></li>
                    <li><span class="hover:text-on-surface">Narwana Bus Stand Desk</span></li>
                    <li><span class="hover:text-on-surface">Rohtak Express Desk</span></li>
                    <li><span class="hover:text-on-surface">Hisar Bypass Hub</span></li>
                    <li><span class="hover:text-on-surface">Delhi NCR Airport Shuttle</span></li>
                </ul>
            </div>
        </div>

        <!-- Bottom Copyright & Security -->
        <div class="pt-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-on-surface-variant">
            <div class="flex flex-wrap items-center gap-2">
                <span>&copy; <?= date('Y') ?> EzSafar Mobility Solutions Pvt. Ltd. All rights reserved.</span>
                <span>•</span>
                <span>ISO 9001:2015 Certified Automotive Fleet</span>
                <span>•</span>
                <a href="<?= BASE_URL ?>/admin/login.php" class="hover:underline">Admin Portal</a>
            </div>
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1 text-emerald-700 font-semibold">
                    <span class="material-symbols-outlined text-[16px]">lock</span> SSL Encrypted
                </span>
                <span class="flex items-center gap-1 text-primary font-semibold">
                    <span class="material-symbols-outlined text-[16px]">verified_user</span> Haryana Transport Endorsed
                </span>
            </div>
        </div>
    </div>
</footer>

<!-- Floating Widgets: WhatsApp & Back to Top -->
<div class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3">
    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" id="back-to-top-btn" title="Back to top" class="w-10 h-10 rounded-full bg-surface-container-highest hover:bg-surface-container text-on-surface shadow-lg flex items-center justify-center transition-all opacity-90 hover:opacity-100">
        <span class="material-symbols-outlined text-[20px]">keyboard_arrow_up</span>
    </button>
    <a href="https://wa.me/<?= htmlspecialchars($fWhatsapp) ?>?text=Hi%20<?= urlencode($fSiteName) ?>,%20I%20am%20interested%20in%20car%20rental%20or%20buying/selling%20a%20car%20in%20<?= urlencode($fCity) ?>" target="_blank" rel="noopener" class="flex items-center gap-2 px-4 py-3 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-xl transition-all hover:scale-105 active:scale-95 group">
        <span class="material-symbols-outlined text-[22px]">chat</span>
        <span class="hidden sm:inline">WhatsApp Desk</span>
    </a>
</div>

</body>
</html>

