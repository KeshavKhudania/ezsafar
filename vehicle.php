<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$db = get_db();

$slug = sanitize($_GET['slug'] ?? '');
$id = (int)($_GET['id'] ?? 0);
$type = sanitize($_GET['type'] ?? '');

$vehicle = null;
$vehicleType = null; // 'rental' or 'used'

if (!empty($slug)) {
    // Check used cars first
    $stmt = $db->prepare("SELECT * FROM used_cars WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    $vehicle = $stmt->fetch();
    if ($vehicle) {
        $vehicleType = 'used';
    } else {
        // Check rental cars
        $stmt = $db->prepare("SELECT * FROM rental_cars WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $vehicle = $stmt->fetch();
        if ($vehicle) {
            $vehicleType = 'rental';
        }
    }
} elseif ($id > 0) {
    if ($type === 'used' || empty($type)) {
        $stmt = $db->prepare("SELECT * FROM used_cars WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $vehicle = $stmt->fetch();
        if ($vehicle) $vehicleType = 'used';
    }
    if (!$vehicle && ($type === 'rental' || empty($type))) {
        $stmt = $db->prepare("SELECT * FROM rental_cars WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $vehicle = $stmt->fetch();
        if ($vehicle) $vehicleType = 'rental';
    }
}

if (!$vehicle) {
    header("HTTP/1.0 404 Not Found");
    $pageTitle = "Vehicle Not Found";
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="max-w-4xl mx-auto px-4 py-20 text-center">
        <span class="material-symbols-outlined text-6xl text-outline">car_crash</span>
        <h1 class="text-3xl font-black text-on-surface mt-4">Vehicle Not Found</h1>
        <p class="text-sm text-on-surface-variant mt-2">The car you are searching for might have been sold or rented out.</p>
        <div class="mt-6 flex justify-center gap-3">
            <a href="' . BASE_URL . '/car-rentals.php" class="px-5 py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow">Browse Rental Fleet</a>
            <a href="' . BASE_URL . '/buy-cars.php" class="px-5 py-2.5 rounded-xl bg-secondary-container text-white text-xs font-bold shadow">Browse Certified Used Cars</a>
        </div>
    </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// SEO Meta Variables
$isRental = ($vehicleType === 'rental');
$displayName = $isRental ? $vehicle['name'] : $vehicle['title'];
$pageTitle = !empty($vehicle['meta_title']) ? $vehicle['meta_title'] : ($isRental ? "Rent $displayName in Jind, Haryana | EzSafar" : "Buy Certified $displayName in Jind | EzSafar");
$pageDesc = !empty($vehicle['meta_description']) ? $vehicle['meta_description'] : ($isRental ? "Book $displayName self-drive or chauffeur car rental in Jind with zero deposit." : "Certified pre-owned $displayName with 140-point inspection in Jind.");
$pageKeywords = !empty($vehicle['meta_keywords']) ? $vehicle['meta_keywords'] : strtolower($displayName) . ", car in jind";
$ogImage = $vehicle['image_url'];
$canonicalUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/vehicle.php?slug=' . urlencode($vehicle['slug']);

// Structured Data (Schema.org Vehicle / Product JSON-LD)
$schemaData = [
    "@context" => "https://schema.org",
    "@type" => "Car",
    "name" => $displayName,
    "image" => $vehicle['image_url'],
    "description" => $pageDesc,
    "brand" => [
        "@type" => "Brand",
        "name" => $vehicle['brand'] ?? explode(' ', $displayName)[0]
    ],
    "model" => $vehicle['model'] ?? $displayName,
    "vehicleTransmission" => $vehicle['transmission'],
    "fuelType" => $vehicle['fuel_type'],
    "offers" => [
        "@type" => "Offer",
        "priceCurrency" => "INR",
        "price" => $isRental ? $vehicle['price_per_day'] : $vehicle['price'],
        "availability" => ($isRental ? $vehicle['is_available'] : ($vehicle['status'] === 'Available')) ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
        "itemCondition" => "https://schema.org/UsedCondition",
        "seller" => [
            "@type" => "AutoDealer",
            "name" => get_setting('site_name', 'EzSafar'),
            "telephone" => get_setting('contact_phone', PHONE_SUPPORT),
            "address" => [
                "@type" => "PostalAddress",
                "streetAddress" => get_setting('primary_hub_address', HUB_LOCATION),
                "addressLocality" => get_setting('primary_city', 'Jind'),
                "addressRegion" => get_setting('primary_state', 'Haryana'),
                "addressCountry" => "IN"
            ]
        ]
    ]
];

if (!$isRental) {
    $schemaData["mileageFromOdometer"] = [
        "@type" => "QuantitativeValue",
        "value" => $vehicle['kms_driven'],
        "unitCode" => "KMT"
    ];
    $schemaData["modelDate"] = (string)$vehicle['year'];
}

$pageStructuredData = json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Inject Schema.org JSON-LD in body for search engines -->
<script type="application/ld+json">
<?= $pageStructuredData ?>
</script>

<div class="max-w-7xl mx-auto px-4 md:px-8 py-6 w-full">
    <!-- Breadcrumb SEO Trail -->
    <nav aria-label="Breadcrumb" class="mb-6 flex items-center gap-2 text-xs text-on-surface-variant">
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-primary flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">home</span>
            <span>Home</span>
        </a>
        <span>/</span>
        <?php if ($isRental): ?>
            <a href="<?= BASE_URL ?>/car-rentals.php" class="hover:text-primary">Car Rentals in Jind</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/buy-cars.php" class="hover:text-primary">Certified Used Cars</a>
        <?php endif; ?>
        <span>/</span>
        <span class="text-on-surface font-bold truncate max-w-xs"><?= htmlspecialchars($displayName) ?></span>
    </nav>

    <!-- Main Product Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left: Imagery & Technical Details -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Hero Car Photo -->
            <div class="relative w-full rounded-3xl bg-surface-container-low border border-outline-variant/15 overflow-hidden flex items-center justify-center p-4 sm:p-6 shadow-sm">
                <img src="<?= htmlspecialchars($vehicle['image_url']) ?>" alt="<?= htmlspecialchars($displayName) ?> - <?= htmlspecialchars(get_setting('primary_city', 'Jind')) ?> Haryana" class="max-h-96 w-full object-contain hover:scale-105 transition-transform duration-500"/>
                
                <div class="absolute top-4 left-4 flex flex-col gap-2">
                    <?php if ($isRental): ?>
                        <span class="px-3 py-1 rounded-full bg-primary text-white text-xs font-bold shadow">
                            <?= htmlspecialchars($vehicle['category']) ?>
                        </span>
                        <span class="px-3 py-1 rounded-full bg-emerald-600 text-white text-xs font-bold shadow flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">verified</span> Zero Security Deposit
                        </span>
                    <?php else: ?>
                        <span class="px-3 py-1 rounded-full bg-emerald-600 text-white text-xs font-bold shadow flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">verified</span> 140-Point Quality Certified
                        </span>
                        <span class="px-3 py-1 rounded-full bg-surface-container-lowest/95 text-on-surface text-xs font-bold shadow">
                            <?= htmlspecialchars($vehicle['registration_state']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($isRental): ?>
                    <span class="absolute bottom-4 right-4 bg-surface-container-lowest/90 backdrop-blur-md px-3 py-1.5 rounded-xl text-xs font-bold text-on-surface shadow flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-amber-500">star</span> <?= $vehicle['rating'] ?> (<?= $vehicle['trips_count'] ?> Verified Trips)
                    </span>
                <?php endif; ?>
            </div>

            <!-- Vehicle Title & Specs Badges -->
            <div>
                <h1 class="text-2xl sm:text-4xl font-black text-on-surface tracking-tight">
                    <?= htmlspecialchars($displayName) ?>
                </h1>
                <p class="text-xs sm:text-sm text-on-surface-variant mt-2">
                    <?php if ($isRental): ?>
                        Available at <?= htmlspecialchars($vehicle['location_hub']) ?>. Sanitized, GPS-tracked, full comprehensive insurance, and Fastag included.
                    <?php else: ?>
                        <?= $vehicle['year'] ?> Model • <?= htmlspecialchars($vehicle['ownership'] ?? '1st Owner') ?> • <?= number_format($vehicle['kms_driven']) ?> km driven • <?= htmlspecialchars($vehicle['color'] ?? 'Original Paint') ?>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Quick Specs Row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-2xl bg-surface-container-low border border-outline-variant/15 text-center text-xs">
                <div class="p-2">
                    <span class="text-on-surface-variant block text-[10px] uppercase font-bold">Fuel Type</span>
                    <strong class="text-on-surface text-sm"><?= htmlspecialchars($vehicle['fuel_type']) ?></strong>
                </div>
                <div class="p-2 border-l border-outline-variant/20">
                    <span class="text-on-surface-variant block text-[10px] uppercase font-bold">Transmission</span>
                    <strong class="text-on-surface text-sm"><?= htmlspecialchars($vehicle['transmission']) ?></strong>
                </div>
                <div class="p-2 border-l border-outline-variant/20">
                    <span class="text-on-surface-variant block text-[10px] uppercase font-bold"><?= $isRental ? 'Seats' : 'Odometer' ?></span>
                    <strong class="text-on-surface text-sm"><?= $isRental ? ($vehicle['seats'] . ' Seater') : (number_format($vehicle['kms_driven']) . ' km') ?></strong>
                </div>
                <div class="p-2 border-l border-outline-variant/20">
                    <span class="text-on-surface-variant block text-[10px] uppercase font-bold"><?= $isRental ? 'Free KM / Day' : 'Body Style' ?></span>
                    <strong class="text-on-surface text-sm"><?= $isRental ? ($vehicle['free_km_per_day'] . ' km') : htmlspecialchars($vehicle['body_type']) ?></strong>
                </div>
            </div>

            <!-- Highlights & Features Matrix -->
            <div class="p-6 rounded-3xl bg-surface-container-lowest border border-outline-variant/15 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">featured_seasonal_and_gifts</span>
                    <span>Key Highlights &amp; Features</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <?php 
                    $feats = $isRental ? explode(',', $vehicle['features'] ?? '') : explode(',', $vehicle['highlights'] ?? '');
                    foreach ($feats as $item): 
                        if (trim($item)):
                    ?>
                        <div class="flex items-center gap-2 p-2.5 rounded-xl bg-surface-container-low text-on-surface">
                            <span class="material-symbols-outlined text-base text-emerald-600 shrink-0">check_circle</span>
                            <span class="font-semibold"><?= htmlspecialchars(trim($item)) ?></span>
                        </div>
                    <?php endif; endforeach; ?>
                </div>

                <?php if (!empty($vehicle['description'])): ?>
                    <div class="pt-4 border-t border-outline-variant/10 text-xs text-on-surface-variant leading-relaxed">
                        <h4 class="font-bold text-on-surface mb-1">Inspection &amp; Vehicle History</h4>
                        <p><?= nl2br(htmlspecialchars($vehicle['description'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Pricing & Immediate Booking Card -->
        <div class="lg:col-span-5 space-y-6">
            <div class="sticky top-28 bg-surface-container-lowest rounded-3xl border border-outline-variant/15 p-6 sm:p-8 shadow-xl space-y-6">
                <!-- Price Header -->
                <div class="pb-5 border-b border-outline-variant/15">
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        <?= $isRental ? 'Daily Rental Rate' : 'Certified All-Inclusive Price' ?>
                    </span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <div class="text-3xl sm:text-4xl font-black text-on-surface">
                            <?= format_rupee($isRental ? $vehicle['price_per_day'] : $vehicle['price']) ?>
                            <?php if ($isRental): ?>
                                <span class="text-xs font-normal text-on-surface-variant">/day</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!$isRental && !empty($vehicle['original_price'])): ?>
                            <span class="text-sm line-through text-on-surface-variant">
                                <?= format_rupee($vehicle['original_price']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if (!$isRental && !empty($vehicle['emi_per_month'])): ?>
                        <div class="mt-2 text-xs font-bold text-primary flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">payments</span>
                            <span>Easy EMI starting from ₹<?= number_format($vehicle['emi_per_month']) ?>/month</span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($isRental): ?>
                    <!-- Quick Rental Booking Box -->
                    <form action="<?= BASE_URL ?>/car-rentals.php" method="POST" class="space-y-4 text-xs">
                        <input type="hidden" name="action" value="book_car"/>
                        <input type="hidden" name="car_id" value="<?= $vehicle['id'] ?>"/>

                        <div>
                            <label class="block font-bold text-on-surface-variant uppercase mb-1">Rental Mode</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="p-2.5 rounded-xl border border-outline-variant/20 bg-surface-container-low flex items-center gap-2 cursor-pointer font-bold">
                                    <input type="radio" name="rental_type" value="Self-Drive" checked class="text-primary"/>
                                    <span>Self-Drive</span>
                                </label>
                                <label class="p-2.5 rounded-xl border border-outline-variant/20 bg-surface-container-low flex items-center gap-2 cursor-pointer font-bold">
                                    <input type="radio" name="rental_type" value="With Chauffeur" class="text-primary"/>
                                    <span>Chauffeur</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-on-surface-variant uppercase mb-1">Full Name *</label>
                            <input type="text" name="customer_name" required placeholder="e.g. Vikas Sharma" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 font-semibold text-on-surface focus:outline-none focus:border-primary"/>
                        </div>

                        <div>
                            <label class="block font-bold text-on-surface-variant uppercase mb-1">WhatsApp Phone *</label>
                            <input type="tel" name="customer_phone" required placeholder="e.g. 9812345678" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 font-semibold text-on-surface focus:outline-none focus:border-primary"/>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-bold text-on-surface-variant uppercase mb-1">Pickup Date</label>
                                <input type="date" name="pickup_date" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>" min="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 rounded-xl bg-surface-container-low border border-outline-variant/20 font-semibold"/>
                            </div>
                            <div>
                                <label class="block font-bold text-on-surface-variant uppercase mb-1">Return Date</label>
                                <input type="date" name="return_date" required value="<?= date('Y-m-d', strtotime('+2 days')) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" class="w-full px-3 py-2 rounded-xl bg-surface-container-low border border-outline-variant/20 font-semibold"/>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-3.5 rounded-xl bg-primary hover:bg-primary-container text-white font-black shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-lg">check_circle</span>
                            <span>Book This Car (Zero Deposit)</span>
                        </button>
                    </form>
                <?php else: ?>
                    <!-- Quick Test Drive / Used Car Inquiry Box -->
                    <form action="<?= BASE_URL ?>/buy-cars.php" method="POST" class="space-y-4 text-xs">
                        <input type="hidden" name="action" value="book_test_drive"/>
                        <input type="hidden" name="car_id" value="<?= $vehicle['id'] ?>"/>

                        <div>
                            <label class="block font-bold text-on-surface-variant uppercase mb-1">Your Full Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Ramesh Mor" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 font-semibold text-on-surface focus:outline-none focus:border-primary"/>
                        </div>

                        <div>
                            <label class="block font-bold text-on-surface-variant uppercase mb-1">WhatsApp Phone *</label>
                            <input type="tel" name="phone" required placeholder="e.g. 9812981298" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 font-semibold text-on-surface focus:outline-none focus:border-primary"/>
                        </div>

                        <div>
                            <label class="block font-bold text-on-surface-variant uppercase mb-1">Preferred Test Drive Date</label>
                            <input type="date" name="preferred_date" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>" min="<?= date('Y-m-d') ?>" class="w-full px-3.5 py-2 rounded-xl bg-surface-container-low border border-outline-variant/20 font-semibold"/>
                        </div>

                        <button type="submit" class="w-full py-3.5 rounded-xl bg-secondary-container hover:bg-secondary text-white font-black shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-lg">airline_seat_recline_normal</span>
                            <span>Schedule Free Test Drive</span>
                        </button>
                    </form>
                <?php endif; ?>

                <!-- WhatsApp Direct Chat Button -->
                <a href="https://wa.me/<?= htmlspecialchars(get_setting('contact_whatsapp', PHONE_WHATSAPP)) ?>?text=Hi%20EzSafar,%20I%20am%20interested%20in%20<?= urlencode($displayName) ?>%20(<?= urlencode($canonicalUrl) ?>)" target="_blank" rel="noopener" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow flex items-center justify-center gap-1.5 transition-all">
                    <span class="material-symbols-outlined text-base">chat</span>
                    <span>Chat on WhatsApp Desk</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

