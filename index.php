<?php
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// Fetch featured rentals
$stmtRentals = $db->query("SELECT * FROM rental_cars WHERE is_available = 1 ORDER BY is_featured DESC, id ASC LIMIT 6");
$rentalCars = $stmtRentals->fetchAll();

// Fetch featured used cars
$stmtUsed = $db->query("SELECT * FROM used_cars WHERE status = 'Available' ORDER BY is_featured DESC, id ASC LIMIT 6");
$usedCars = $stmtUsed->fetchAll();

// Fetch active hubs
$stmtHubs = $db->query("SELECT * FROM hubs WHERE is_active = 1 ORDER BY city ASC");
$hubs = $stmtHubs->fetchAll();

// Fetch latest blog posts
$recentPosts = $db->query("
    SELECT p.*, c.name as category_name 
    FROM blog_posts p 
    LEFT JOIN blog_categories c ON p.category_id = c.id 
    WHERE p.is_published = 1 
    ORDER BY p.published_at DESC 
    LIMIT 3
")->fetchAll();
?>

<div class="flex flex-col w-full">
    <!-- HERO & MAKE-MY-TRIP STYLE SEARCH ECOSYSTEM -->
    <section class="relative w-full overflow-hidden bg-gradient-to-b from-surface-container-low via-surface to-background pb-16 pt-6">
        <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-primary-fixed/40 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-20 top-72 h-80 w-80 rounded-full bg-secondary-fixed/50 blur-3xl"></div>

        <div class="mx-auto max-w-7xl px-4 md:px-8">
            <!-- Header assurance & badge -->
            <div class="flex flex-col items-center text-center">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-surface-container px-4 py-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-base text-secondary-container" style="font-variation-settings: 'FILL' 1;">verified</span>
                    <span class="text-xs sm:text-sm font-semibold text-on-surface">Haryana's #1 Rated Self-Drive &amp; Certified Car Hub</span>
                    <span class="rounded bg-tertiary-fixed px-1.5 py-0.5 text-[10px] font-bold text-on-tertiary-fixed">JIND HUB ACTIVE</span>
                </div>
                <h1 class="max-w-4xl text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-on-surface">
                    Your Journey, <span class="text-primary">Your Car</span>, <span class="text-secondary-container">Your Way.</span>
                </h1>
                <p class="mt-4 max-w-2xl text-sm sm:text-base text-on-surface-variant">
                    Jind's premier mobility portal for verified self-drive car rentals and zero-hassle pre-owned vehicle trading with transparent per-kilometer plans, zero deposit options, and doorstep delivery across Haryana.
                </p>

                <!-- Trust Chips -->
                <div class="mt-6 flex flex-wrap items-center justify-center gap-3 sm:gap-4 text-xs font-semibold text-on-surface-variant">
                    <span class="flex items-center gap-1.5 rounded-full bg-surface-container-lowest px-3 py-1.5 shadow-sm border border-outline-variant/10">
                        <span class="material-symbols-outlined text-amber-500 text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                        <strong class="text-on-surface">4.9/5</strong> Rating (1,400+ Jind Reviews)
                    </span>
                    <span class="flex items-center gap-1.5 rounded-full bg-surface-container-lowest px-3 py-1.5 shadow-sm border border-outline-variant/10">
                        <span class="material-symbols-outlined text-emerald-600 text-sm">flight_takeoff</span>
                        <strong class="text-on-surface">5,000+</strong> Happy Safars Done
                    </span>
                    <span class="flex items-center gap-1.5 rounded-full bg-surface-container-lowest px-3 py-1.5 shadow-sm border border-outline-variant/10">
                        <span class="material-symbols-outlined text-primary text-sm">verified</span>
                        <strong class="text-on-surface">100%</strong> Commercial Fleet
                    </span>
                    <span class="flex items-center gap-1.5 rounded-full bg-surface-container-lowest px-3 py-1.5 shadow-sm border border-outline-variant/10">
                        <span class="material-symbols-outlined text-primary text-sm">support_agent</span>
                        <strong class="text-on-surface">24x7</strong> On-Road Assistance
                    </span>
                </div>
            </div>

            <!-- Elevated Search Engine Tabs -->
            <div class="mt-10 mx-auto max-w-5xl rounded-3xl bg-surface-container-lowest p-3 sm:p-6 shadow-xl border border-outline-variant/15" id="search-box">
                <!-- Tab Headers -->
                <div class="flex items-center gap-2 border-b border-outline-variant/15 pb-4 overflow-x-auto">
                    <button type="button" onclick="switchHeroTab('rental-tab')" id="tab-btn-rental" class="flex items-center gap-2 px-5 py-2.5 rounded-2xl font-bold text-sm transition-all bg-primary text-white shadow-md">
                        <span class="material-symbols-outlined text-[20px]">car_rental</span>
                        <span>Rent a Car (Self-Drive)</span>
                    </button>
                    <button type="button" onclick="switchHeroTab('buy-tab')" id="tab-btn-buy" class="flex items-center gap-2 px-5 py-2.5 rounded-2xl font-bold text-sm transition-all text-on-surface-variant hover:bg-surface-container hover:text-on-surface">
                        <span class="material-symbols-outlined text-[20px]">directions_car</span>
                        <span>Buy Certified Used Cars</span>
                    </button>
                    <button type="button" onclick="switchHeroTab('sell-tab')" id="tab-btn-sell" class="flex items-center gap-2 px-5 py-2.5 rounded-2xl font-bold text-sm transition-all text-on-surface-variant hover:bg-surface-container hover:text-on-surface">
                        <span class="material-symbols-outlined text-[20px]">sell</span>
                        <span>Sell Your Car</span>
                    </button>
                </div>

                <!-- Tab 1: Rent Car Form -->
                <form action="<?= BASE_URL ?>/car-rentals.php" method="GET" id="rental-tab" class="pt-5 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="flex flex-col gap-1 p-3 rounded-2xl bg-surface-container-low border border-outline-variant/20 hover:border-primary transition-colors">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-primary">location_on</span> Pickup Hub
                        </label>
                        <select name="hub" class="bg-transparent text-sm font-semibold text-on-surface focus:outline-none cursor-pointer">
                            <?php foreach ($hubs as $hub): ?>
                                <option value="<?= htmlspecialchars($hub['hub_name']) ?>"><?= htmlspecialchars($hub['city']) ?> - <?= htmlspecialchars($hub['hub_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1 p-3 rounded-2xl bg-surface-container-low border border-outline-variant/20 hover:border-primary transition-colors">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-primary">calendar_today</span> Pickup Date
                        </label>
                        <input type="date" name="pickup_date" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" min="<?= date('Y-m-d') ?>" class="bg-transparent text-sm font-semibold text-on-surface focus:outline-none"/>
                    </div>

                    <div class="flex flex-col gap-1 p-3 rounded-2xl bg-surface-container-low border border-outline-variant/20 hover:border-primary transition-colors">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-primary">event</span> Return Date
                        </label>
                        <input type="date" name="return_date" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" class="bg-transparent text-sm font-semibold text-on-surface focus:outline-none"/>
                    </div>

                    <div class="flex items-center">
                        <button type="submit" class="w-full h-full py-4 rounded-2xl bg-primary hover:bg-primary-container text-white font-bold text-sm shadow-lg shadow-primary/25 transition-all flex items-center justify-center gap-2 active:scale-95">
                            <span class="material-symbols-outlined text-xl">search</span>
                            <span>Find Available Fleet</span>
                        </button>
                    </div>
                </form>

                <!-- Tab 2: Buy Used Car Form -->
                <form action="<?= BASE_URL ?>/buy-cars.php" method="GET" id="buy-tab" class="hidden pt-5 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="flex flex-col gap-1 p-3 rounded-2xl bg-surface-container-low border border-outline-variant/20">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Brand</label>
                        <select name="brand" class="bg-transparent text-sm font-semibold text-on-surface focus:outline-none cursor-pointer">
                            <option value="">All Brands</option>
                            <option value="Maruti Suzuki">Maruti Suzuki</option>
                            <option value="Hyundai">Hyundai</option>
                            <option value="Mahindra">Mahindra</option>
                            <option value="Honda">Honda</option>
                            <option value="Toyota">Toyota</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1 p-3 rounded-2xl bg-surface-container-low border border-outline-variant/20">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Budget</label>
                        <select name="budget" class="bg-transparent text-sm font-semibold text-on-surface focus:outline-none cursor-pointer">
                            <option value="">Any Budget</option>
                            <option value="under-5">Under ₹ 5 Lakh</option>
                            <option value="5-10">₹ 5 Lakh - ₹ 10 Lakh</option>
                            <option value="10-15">₹ 10 Lakh - ₹ 15 Lakh</option>
                            <option value="above-15">Above ₹ 15 Lakh</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1 p-3 rounded-2xl bg-surface-container-low border border-outline-variant/20">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Body Type</label>
                        <select name="body_type" class="bg-transparent text-sm font-semibold text-on-surface focus:outline-none cursor-pointer">
                            <option value="">All Body Types</option>
                            <option value="Hatchback">Hatchback</option>
                            <option value="Sedan">Sedan</option>
                            <option value="SUV">SUV</option>
                        </select>
                    </div>

                    <div class="flex items-center">
                        <button type="submit" class="w-full h-full py-4 rounded-2xl bg-secondary-container hover:bg-secondary text-on-secondary font-bold text-sm shadow-lg transition-all flex items-center justify-center gap-2 active:scale-95">
                            <span class="material-symbols-outlined text-xl">manage_search</span>
                            <span>Explore 140-Pt Cars</span>
                        </button>
                    </div>
                </form>

                <!-- Tab 3: Sell Your Car Form -->
                <form action="<?= BASE_URL ?>/sell-your-car.php" method="GET" id="sell-tab" class="hidden pt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex flex-col gap-1 p-3 rounded-2xl bg-surface-container-low border border-outline-variant/20 md:col-span-2">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Vehicle Registration Number</label>
                        <input type="text" name="reg" placeholder="e.g. HR-31-AB-1234" required class="bg-transparent text-sm font-bold uppercase text-on-surface focus:outline-none"/>
                    </div>
                    <div class="flex items-center">
                        <button type="submit" class="w-full h-full py-4 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm shadow-lg transition-all flex items-center justify-center gap-2 active:scale-95">
                            <span class="material-symbols-outlined text-xl">price_check</span>
                            <span>Get Free Price Estimate</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- SECTION: FEATURED SELF-DRIVE RENTAL FLEET -->
    <section class="max-w-7xl mx-auto px-4 md:px-8 py-16 w-full">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-fixed text-on-primary-fixed text-xs font-bold mb-2">
                    <span class="material-symbols-outlined text-sm">directions_car</span> ZERO SECURITY DEPOSIT
                </div>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-on-surface tracking-tight">
                    Popular Self-Drive Rental Cars in Jind
                </h2>
                <p class="text-sm text-on-surface-variant mt-1">
                    Book sanitized, GPS-equipped commercial fleet for local trips, weddings, or outstation tours.
                </p>
            </div>
            <a href="<?= BASE_URL ?>/car-rentals.php" class="inline-flex items-center gap-1 text-primary font-bold text-sm hover:underline">
                View Full Fleet (<?= count($rentalCars) ?>+ Available) <span class="material-symbols-outlined text-base">arrow_forward</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($rentalCars as $car): ?>
                <div class="rounded-3xl bg-surface-container-lowest border border-outline-variant/15 p-5 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                    <div>
                        <!-- Image Container -->
                        <a href="<?= BASE_URL ?>/vehicle.php?slug=<?= urlencode($car['slug']) ?>" class="relative w-full h-48 rounded-2xl bg-surface-container-low overflow-hidden flex items-center justify-center p-2 mb-4 block">
                            <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['name']) ?>" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-300"/>
                            <span class="absolute top-3 left-3 bg-surface-container-lowest/90 backdrop-blur-md px-2.5 py-1 rounded-full text-[11px] font-bold text-primary shadow-sm">
                                <?= htmlspecialchars($car['category']) ?>
                            </span>
                            <span class="absolute top-3 right-3 bg-emerald-50 text-emerald-800 border border-emerald-200 px-2 py-0.5 rounded-full text-[11px] font-bold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">star</span> <?= $car['rating'] ?> (<?= $car['trips_count'] ?> trips)
                            </span>
                        </a>

                        <!-- Title & Specs -->
                        <h3 class="text-lg font-bold text-on-surface group-hover:text-primary transition-colors">
                            <a href="<?= BASE_URL ?>/vehicle.php?slug=<?= urlencode($car['slug']) ?>">
                                <?= htmlspecialchars($car['name']) ?>
                            </a>
                        </h3>
                        <div class="flex items-center gap-3 mt-2 text-xs font-medium text-on-surface-variant">
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">local_gas_station</span> <?= htmlspecialchars($car['fuel_type']) ?></span>
                            <span>•</span>
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">settings</span> <?= htmlspecialchars($car['transmission']) ?></span>
                            <span>•</span>
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">group</span> <?= $car['seats'] ?> Seats</span>
                        </div>

                        <!-- Per day inclusions -->
                        <div class="mt-4 pt-3 border-t border-outline-variant/10 text-xs text-on-surface-variant flex items-center justify-between">
                            <span>Includes <?= $car['free_km_per_day'] ?> km / day</span>
                            <span class="font-semibold text-emerald-700">₹0 Security Deposit</span>
                        </div>
                    </div>

                    <!-- Price & Action -->
                    <div class="mt-6 pt-4 border-t border-outline-variant/15 flex items-center justify-between gap-3">
                        <div>
                            <div class="text-xs text-on-surface-variant">Starts at</div>
                            <div class="text-2xl font-black text-on-surface">
                                <?= format_rupee($car['price_per_day']) ?><span class="text-xs font-normal text-on-surface-variant">/day</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="<?= BASE_URL ?>/vehicle.php?slug=<?= urlencode($car['slug']) ?>" class="px-3 py-2.5 rounded-xl border border-outline-variant/30 hover:bg-surface-container text-on-surface text-xs font-bold transition-all" title="View vehicle specifications &amp; gallery">
                                Specs
                            </a>
                            <a href="<?= BASE_URL ?>/car-rentals.php?book_car=<?= $car['id'] ?>" class="px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold shadow-md transition-all active:scale-95">
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- SECTION: 140-POINT INSPECTION & TRUST PILLARS -->
    <section class="w-full bg-surface-container-low py-16 border-y border-outline-variant/15">
        <div class="max-w-7xl mx-auto px-4 md:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="text-xs font-extrabold uppercase tracking-widest text-secondary-container">THE EZSAFAR GUARANTEE</span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-on-surface mt-2 tracking-tight">
                    Why Haryana Trusts EzSafar
                </h2>
                <p class="text-sm text-on-surface-variant mt-2">
                    Every certified pre-owned car undergoes rigorous multi-point testing at our Jind workshop before listing.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="p-6 rounded-3xl bg-surface-container-lowest border border-outline-variant/15 shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-2xl">verified_user</span>
                    </div>
                    <h3 class="font-bold text-base text-on-surface">140-Point Inspection</h3>
                    <p class="text-xs text-on-surface-variant mt-2 leading-relaxed">
                        Engine compression, transmission health, electrical systems, suspension, and non-flood chassis certification.
                    </p>
                </div>

                <div class="p-6 rounded-3xl bg-surface-container-lowest border border-outline-variant/15 shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-secondary-container/15 text-secondary-container flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-2xl">published_with_changes</span>
                    </div>
                    <h3 class="font-bold text-base text-on-surface">7-Day Return Guarantee</h3>
                    <p class="text-xs text-on-surface-variant mt-2 leading-relaxed">
                        Love your purchased car or return it within 7 days for a 100% money-back guarantee. No awkward questions asked.
                    </p>
                </div>

                <div class="p-6 rounded-3xl bg-surface-container-lowest border border-outline-variant/15 shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600/15 text-emerald-700 flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-2xl">assignment_turned_in</span>
                    </div>
                    <h3 class="font-bold text-base text-on-surface">Free Haryana RC Transfer</h3>
                    <p class="text-xs text-on-surface-variant mt-2 leading-relaxed">
                        Complete RTO documentation in Jind, Narwana, Rohtak, and Delhi NCR handled smoothly by our dedicated legal desk.
                    </p>
                </div>

                <div class="p-6 rounded-3xl bg-surface-container-lowest border border-outline-variant/15 shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/15 text-amber-700 flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-2xl">shield_with_heart</span>
                    </div>
                    <h3 class="font-bold text-base text-on-surface">1-Year Warranty Included</h3>
                    <p class="text-xs text-on-surface-variant mt-2 leading-relaxed">
                        Comprehensive coverage for engine, transmission, steering rack, and major electrical assemblies across all certified cars.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION: BUY CERTIFIED USED CARS -->
    <section class="max-w-7xl mx-auto px-4 md:px-8 py-16 w-full">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-fixed text-on-secondary-fixed text-xs font-bold mb-2">
                    <span class="material-symbols-outlined text-sm">workspace_premium</span> 140-POINT INSPECTED
                </div>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-on-surface tracking-tight">
                    Buy Certified Used Cars in Jind
                </h2>
                <p class="text-sm text-on-surface-variant mt-1">
                    Inspected, non-accidental certified vehicles with easy financing starting at low EMIs.
                </p>
            </div>
            <a href="<?= BASE_URL ?>/buy-cars.php" class="inline-flex items-center gap-1 text-primary font-bold text-sm hover:underline">
                View All Certified Cars (<?= count($usedCars) ?> Available) <span class="material-symbols-outlined text-base">arrow_forward</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($usedCars as $car): ?>
                <div class="rounded-3xl bg-surface-container-lowest border border-outline-variant/15 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                    <div>
                        <!-- Image Container with Badges -->
                        <a href="<?= BASE_URL ?>/vehicle.php?slug=<?= urlencode($car['slug']) ?>" class="relative w-full h-56 bg-surface-container-low overflow-hidden block">
                            <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                            <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                                <span class="bg-emerald-600 text-white px-2.5 py-0.5 rounded-full text-[10px] font-bold shadow flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[13px]">verified</span> 140-Pt Certified
                                </span>
                                <span class="bg-surface-container-lowest/90 backdrop-blur-md px-2.5 py-0.5 rounded-full text-[10px] font-bold text-on-surface shadow">
                                    <?= htmlspecialchars($car['registration_state']) ?>
                                </span>
                            </div>
                            <span class="absolute bottom-3 right-3 bg-surface-container-lowest/90 backdrop-blur-md text-xs font-bold px-2.5 py-1 rounded-xl text-primary shadow">
                                EMI from ₹<?= number_format($car['emi_per_month'] ?? 12000) ?>/mo
                            </span>
                        </a>

                        <!-- Card Body -->
                        <div class="p-5">
                            <div class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                                <?= $car['year'] ?> • <?= htmlspecialchars($car['ownership']) ?>
                            </div>
                            <h3 class="text-lg font-bold text-on-surface mt-1 group-hover:text-primary transition-colors">
                                <a href="<?= BASE_URL ?>/vehicle.php?slug=<?= urlencode($car['slug']) ?>">
                                    <?= htmlspecialchars($car['title']) ?>
                                </a>
                            </h3>

                            <div class="grid grid-cols-3 gap-2 mt-4 p-3 rounded-2xl bg-surface-container-low text-center text-xs">
                                <div>
                                    <span class="text-on-surface-variant block text-[10px]">KMs</span>
                                    <span class="font-bold text-on-surface"><?= number_format($car['kms_driven']) ?> km</span>
                                </div>
                                <div class="border-x border-outline-variant/20">
                                    <span class="text-on-surface-variant block text-[10px]">Fuel</span>
                                    <span class="font-bold text-on-surface"><?= htmlspecialchars($car['fuel_type']) ?></span>
                                </div>
                                <div>
                                    <span class="text-on-surface-variant block text-[10px]">Gearbox</span>
                                    <span class="font-bold text-on-surface"><?= htmlspecialchars($car['transmission']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Price & CTA -->
                    <div class="p-5 pt-0">
                        <div class="pt-4 border-t border-outline-variant/15 flex items-center justify-between gap-3">
                            <div>
                                <div class="text-2xl font-black text-on-surface">
                                    <?= format_rupee($car['price']) ?>
                                </div>
                                <?php if (!empty($car['original_price'])): ?>
                                    <div class="text-xs text-on-surface-variant line-through">
                                        <?= format_rupee($car['original_price']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="<?= BASE_URL ?>/vehicle.php?slug=<?= urlencode($car['slug']) ?>" class="px-3 py-2.5 rounded-xl border border-outline-variant/30 hover:bg-surface-container text-on-surface text-xs font-bold transition-all" title="View 140-point inspection &amp; full details">
                                    Details
                                </a>
                                <a href="<?= BASE_URL ?>/buy-cars.php?test_drive=<?= $car['id'] ?>" class="px-4 py-2.5 rounded-xl bg-secondary-container hover:bg-secondary text-on-secondary text-xs font-bold shadow transition-all active:scale-95 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">airline_seat_recline_normal</span>
                                    <span>Test Drive</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- SECTION: LATEST FROM BLOG & GUIDES -->
    <?php if (!empty($recentPosts)): ?>
        <section class="max-w-7xl mx-auto px-4 md:px-8 py-14 w-full">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-fixed text-on-primary-fixed text-xs font-bold mb-2">
                        <span class="material-symbols-outlined text-sm">menu_book</span> AUTOMOTIVE KNOWLEDGE &amp; TRAVEL
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-on-surface tracking-tight">
                        Latest from EzSafar Knowledge Hub
                    </h2>
                    <p class="text-sm text-on-surface-variant mt-1">
                        Helpful guides on self-drive car rentals, vehicle inspection, Haryana RTO regulations, and weekend road trips.
                    </p>
                </div>
                <a href="<?= BASE_URL ?>/blog.php" class="inline-flex items-center gap-1 text-primary font-bold text-sm hover:underline">
                    View All Articles (<?= count($recentPosts) ?>+) <span class="material-symbols-outlined text-base">arrow_forward</span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($recentPosts as $bp): ?>
                    <article class="rounded-3xl bg-surface-container-lowest border border-outline-variant/15 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            <a href="<?= BASE_URL ?>/blog-post.php?slug=<?= urlencode($bp['slug']) ?>" class="relative block w-full h-48 bg-surface-container-low overflow-hidden">
                                <img src="<?= htmlspecialchars($bp['featured_image']) ?>" alt="<?= htmlspecialchars($bp['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                                <span class="absolute top-3 left-3 bg-surface-container-lowest/90 backdrop-blur-md px-2.5 py-1 rounded-full text-[10px] font-bold text-primary shadow">
                                    <?= htmlspecialchars($bp['category_name'] ?? 'Guide') ?>
                                </span>
                                <span class="absolute bottom-3 right-3 bg-black/60 backdrop-blur-md text-white px-2 py-0.5 rounded-lg text-[10px] font-semibold flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[13px]">schedule</span> <?= $bp['reading_time'] ?> min read
                                </span>
                            </a>
                            <div class="p-5">
                                <div class="text-[11px] text-on-surface-variant mb-1">
                                    <?= date('M d, Y', strtotime($bp['published_at'])) ?> • <?= $bp['views_count'] ?> views
                                </div>
                                <h3 class="text-base font-bold text-on-surface leading-snug group-hover:text-primary transition-colors">
                                    <a href="<?= BASE_URL ?>/blog-post.php?slug=<?= urlencode($bp['slug']) ?>">
                                        <?= htmlspecialchars($bp['title']) ?>
                                    </a>
                                </h3>
                                <p class="text-xs text-on-surface-variant mt-2 line-clamp-2 leading-relaxed">
                                    <?= htmlspecialchars($bp['excerpt']) ?>
                                </p>
                            </div>
                        </div>
                        <div class="p-5 pt-0">
                            <div class="pt-3 border-t border-outline-variant/15 flex items-center justify-between text-xs">
                                <span class="font-semibold text-on-surface-variant"><?= htmlspecialchars($bp['author_name']) ?></span>
                                <a href="<?= BASE_URL ?>/blog-post.php?slug=<?= urlencode($bp['slug']) ?>" class="text-primary font-bold inline-flex items-center gap-1 hover:underline">
                                    <span>Read Article</span>
                                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- SECTION: SELL YOUR CAR FAST BANNER -->
    <section class="max-w-7xl mx-auto px-4 md:px-8 py-8 w-full">
        <div class="rounded-3xl bg-gradient-to-r from-primary via-primary-container to-surface-tint p-8 sm:p-12 text-white relative overflow-hidden shadow-2xl flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="max-w-xl z-10">
                <span class="px-3 py-1 rounded-full bg-white/20 text-xs font-bold uppercase tracking-wider backdrop-blur-md">Instant Bank Transfer</span>
                <h2 class="text-2xl sm:text-4xl font-extrabold mt-3 leading-tight">
                    Want to Sell Your Car in Jind at the Best Market Price?
                </h2>
                <p class="mt-2 text-white/90 text-sm sm:text-base leading-relaxed">
                    Skip dealer commissions. Get instant doorstep evaluation in Jind, guaranteed highest price match, and payment directly to your bank account within 60 minutes.
                </p>
                <div class="mt-6 flex flex-wrap gap-4">
                    <a href="<?= BASE_URL ?>/sell-your-car.php" class="px-6 py-3.5 rounded-2xl bg-white text-primary font-bold text-sm shadow-xl hover:bg-surface-container-low transition-all active:scale-95 flex items-center gap-2">
                        <span class="material-symbols-outlined">calculate</span>
                        <span>Get Instant Valuation Now</span>
                    </a>
                    <a href="tel:<?= PHONE_SUPPORT ?>" class="px-6 py-3.5 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/25 text-white font-bold text-sm backdrop-blur-md transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined">call</span>
                        <span>Call Valuation Team</span>
                    </a>
                </div>
            </div>
            <div class="relative z-10 hidden lg:block">
                <div class="w-72 h-44 rounded-2xl bg-white/10 backdrop-blur-lg border border-white/20 p-5 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-white/80">Average Payout Time</span>
                        <span class="material-symbols-outlined text-tertiary-fixed-dim">timer</span>
                    </div>
                    <div class="text-3xl font-black">60 Minutes</div>
                    <div class="text-xs text-white/70">From final inspection to instant NEFT / IMPS credit. Free RC Transfer guarantee.</div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function switchHeroTab(tabId) {
    ['rental-tab', 'buy-tab', 'sell-tab'].forEach(id => {
        document.getElementById(id).classList.add('hidden');
    });
    document.getElementById(tabId).classList.remove('hidden');

    ['tab-btn-rental', 'tab-btn-buy', 'tab-btn-sell'].forEach(id => {
        const btn = document.getElementById(id);
        btn.classList.remove('bg-primary', 'text-white', 'shadow-md');
        btn.classList.add('text-on-surface-variant');
    });

    const activeBtn = {
        'rental-tab': 'tab-btn-rental',
        'buy-tab': 'tab-btn-buy',
        'sell-tab': 'tab-btn-sell'
    }[tabId];

    const current = document.getElementById(activeBtn);
    current.classList.add('bg-primary', 'text-white', 'shadow-md');
    current.classList.remove('text-on-surface-variant');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

