<?php
$pageTitle = "Car Rentals in Jind, Haryana | Self-Drive & Chauffeur Fleet";
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// Handle Booking Form Submission
$bookingSuccess = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_car') {
    $carId = (int)($_POST['car_id'] ?? 0);
    $customerName = sanitize($_POST['customer_name'] ?? '');
    $customerPhone = sanitize($_POST['customer_phone'] ?? '');
    $customerEmail = sanitize($_POST['customer_email'] ?? '');
    $customerCity = sanitize($_POST['customer_city'] ?? 'Jind');
    $rentalType = sanitize($_POST['rental_type'] ?? 'Self-Drive');
    $pickupDate = sanitize($_POST['pickup_date'] ?? date('Y-m-d'));
    $pickupTime = sanitize($_POST['pickup_time'] ?? '09:00:00');
    $returnDate = sanitize($_POST['return_date'] ?? date('Y-m-d', strtotime('+1 day')));
    $returnTime = sanitize($_POST['return_time'] ?? '20:00:00');
    $pickupLoc = sanitize($_POST['pickup_location'] ?? 'Jind Central Mobility Hub');
    $dropLoc = sanitize($_POST['drop_location'] ?? 'Same as Pickup');
    $specialRequests = sanitize($_POST['special_requests'] ?? '');

    // Validate car
    $stmtC = $db->prepare("SELECT * FROM rental_cars WHERE id = ?");
    $stmtC->execute([$carId]);
    $car = $stmtC->fetch();

    if ($car && !empty($customerName) && !empty($customerPhone)) {
        // Calculate days
        $start = new DateTime($pickupDate . ' ' . $pickupTime);
        $end = new DateTime($returnDate . ' ' . $returnTime);
        $interval = $start->diff($end);
        $days = max(1, (int)$interval->days);
        if ($interval->h > 2) {
            $days++;
        }

        $totalAmount = $days * (float)$car['price_per_day'];
        if ($rentalType === 'With Chauffeur') {
            $totalAmount += ($days * 600); // Chauffeur allowance per day
        }

        $bookingNumber = generate_reference_id('EZB');

        $stmtIns = $db->prepare("INSERT INTO rental_bookings 
            (booking_number, car_id, rental_type, customer_name, customer_phone, customer_email, customer_city, pickup_date, pickup_time, return_date, return_time, pickup_location, drop_location, total_days, total_amount, special_requests, booking_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Confirmed')");
        $stmtIns->execute([
            $bookingNumber, $carId, $rentalType, $customerName, $customerPhone, $customerEmail, $customerCity,
            $pickupDate, $pickupTime, $returnDate, $returnTime, $pickupLoc, $dropLoc, $days, $totalAmount, $specialRequests
        ]);

        $bookingSuccess = [
            'booking_number' => $bookingNumber,
            'car_name' => $car['name'],
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'days' => $days,
            'total_amount' => $totalAmount,
            'pickup_date' => $pickupDate . ' at ' . $pickupTime,
            'return_date' => $returnDate . ' at ' . $returnTime,
            'location' => $pickupLoc
        ];

        set_flash('success', "Booking confirmed successfully! Your Booking ID is $bookingNumber. Our Jind fleet desk will contact you shortly.");
    } else {
        set_flash('error', "Please fill in all required fields to complete your reservation.");
    }
}

// Filters
$categoryFilter = sanitize($_GET['cat'] ?? '');
$fuelFilter = sanitize($_GET['fuel'] ?? '');
$transFilter = sanitize($_GET['transmission'] ?? '');
$search = sanitize($_GET['q'] ?? '');

$sql = "SELECT * FROM rental_cars WHERE is_available = 1";
$params = [];

if ($categoryFilter) {
    $sql .= " AND category = ?";
    $params[] = $categoryFilter;
}
if ($fuelFilter) {
    $sql .= " AND fuel_type = ?";
    $params[] = $fuelFilter;
}
if ($transFilter) {
    $sql .= " AND transmission = ?";
    $params[] = $transFilter;
}
if ($search) {
    $sql .= " AND (name LIKE ? OR features LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY price_per_day ASC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$fleet = $stmt->fetchAll();

// Preselected car if query param present
$preselectedCarId = (int)($_GET['book_car'] ?? 0);
?>

<div class="max-w-7xl mx-auto px-4 md:px-8 py-8 w-full">
    <!-- Success Booking Modal Overlay -->
    <?php if ($bookingSuccess): ?>
        <div class="mb-8 p-6 sm:p-8 rounded-3xl bg-emerald-50 border-2 border-emerald-300 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-lg">
                    <span class="material-symbols-outlined text-3xl">task_alt</span>
                </div>
                <div>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-200 text-emerald-900 text-xs font-bold uppercase tracking-wider">Booking Confirmed</span>
                    <h2 class="text-2xl font-black text-emerald-950 mt-1">Safar Booked: <?= htmlspecialchars($bookingSuccess['car_name']) ?></h2>
                    <p class="text-sm text-emerald-800 mt-1">
                        Booking ID: <strong class="text-emerald-950 font-mono text-base"><?= $bookingSuccess['booking_number'] ?></strong> • 
                        Total: <strong><?= format_rupee($bookingSuccess['total_amount']) ?></strong> (<?= $bookingSuccess['days'] ?> Days)
                    </p>
                    <p class="text-xs text-emerald-700 mt-1">
                        Pickup on <?= $bookingSuccess['pickup_date'] ?> from <?= htmlspecialchars($bookingSuccess['location']) ?>. Please carry your Driving License and Aadhaar Card.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="https://wa.me/<?= PHONE_WHATSAPP ?>?text=Hello%20EzSafar,%20I%20have%20booked%20<?= urlencode($bookingSuccess['booking_number']) ?>%20for%20<?= urlencode($bookingSuccess['car_name']) ?>" target="_blank" rel="noopener" class="px-5 py-3 rounded-xl bg-emerald-700 text-white font-bold text-xs shadow hover:bg-emerald-800 transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">chat</span> Connect on WhatsApp
                </a>
                <button onclick="window.print()" class="px-4 py-3 rounded-xl bg-white border border-emerald-300 text-emerald-900 font-bold text-xs shadow-sm hover:bg-emerald-100">
                    Print Receipt
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Page Header & Fleet Filter Navigation -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-6 border-b border-outline-variant/15">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-fixed text-on-primary-fixed text-xs font-bold mb-2">
                <span class="material-symbols-outlined text-sm">directions_car</span> ZERO DEPOSIT • UNLIMITED FREEDOM
            </div>
            <h1 class="text-3xl sm:text-5xl font-black text-on-surface tracking-tight">
                Self-Drive &amp; Chauffeur Car Rentals in Jind
            </h1>
            <p class="text-sm text-on-surface-variant mt-2 max-w-2xl">
                Choose from our verified fleet of hatchbacks, executive sedans, rugged 4x4 Thars, and 7-seater Innova Crystas. All cars come with Fastag and full comprehensive insurance.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-on-surface-variant">Fleet count:</span>
            <span class="px-3 py-1 rounded-full bg-surface-container font-bold text-sm text-primary"><?= count($fleet) ?> Available</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="my-6 p-4 rounded-3xl bg-surface-container-low border border-outline-variant/15 flex flex-wrap items-center justify-between gap-4">
        <!-- Categories -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= BASE_URL ?>/car-rentals.php" class="px-4 py-2 rounded-xl text-xs font-bold transition-all <?= empty($categoryFilter) ? 'bg-primary text-white shadow' : 'bg-surface-container-lowest text-on-surface-variant hover:text-on-surface' ?>">
                All Categories
            </a>
            <a href="<?= BASE_URL ?>/car-rentals.php?cat=Hatchback" class="px-4 py-2 rounded-xl text-xs font-bold transition-all <?= ($categoryFilter === 'Hatchback') ? 'bg-primary text-white shadow' : 'bg-surface-container-lowest text-on-surface-variant hover:text-on-surface' ?>">
                Hatchbacks
            </a>
            <a href="<?= BASE_URL ?>/car-rentals.php?cat=Sedan" class="px-4 py-2 rounded-xl text-xs font-bold transition-all <?= ($categoryFilter === 'Sedan') ? 'bg-primary text-white shadow' : 'bg-surface-container-lowest text-on-surface-variant hover:text-on-surface' ?>">
                Sedans
            </a>
            <a href="<?= BASE_URL ?>/car-rentals.php?cat=Compact+SUV" class="px-4 py-2 rounded-xl text-xs font-bold transition-all <?= ($categoryFilter === 'Compact SUV') ? 'bg-primary text-white shadow' : 'bg-surface-container-lowest text-on-surface-variant hover:text-on-surface' ?>">
                Compact SUVs
            </a>
            <a href="<?= BASE_URL ?>/car-rentals.php?cat=Luxury+SUV" class="px-4 py-2 rounded-xl text-xs font-bold transition-all <?= ($categoryFilter === 'Luxury SUV') ? 'bg-primary text-white shadow' : 'bg-surface-container-lowest text-on-surface-variant hover:text-on-surface' ?>">
                Luxury &amp; 4x4 Thar
            </a>
            <a href="<?= BASE_URL ?>/car-rentals.php?cat=MUV+%2F+7-Seater" class="px-4 py-2 rounded-xl text-xs font-bold transition-all <?= ($categoryFilter === 'MUV / 7-Seater') ? 'bg-primary text-white shadow' : 'bg-surface-container-lowest text-on-surface-variant hover:text-on-surface' ?>">
                7-Seaters (Innova)
            </a>
        </div>

        <!-- Quick Search -->
        <form action="<?= BASE_URL ?>/car-rentals.php" method="GET" class="flex items-center gap-2 w-full md:w-auto">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search Swift, Thar, Creta..." class="px-4 py-2 rounded-xl bg-surface-container-lowest border border-outline-variant/20 text-xs text-on-surface focus:outline-none focus:border-primary w-full md:w-56"/>
            <button type="submit" class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold shadow hover:bg-primary-container">
                Search
            </button>
        </form>
    </div>

    <!-- Cars Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (empty($fleet)): ?>
            <div class="col-span-full py-16 text-center">
                <span class="material-symbols-outlined text-5xl text-outline">no_crash</span>
                <h3 class="text-xl font-bold mt-2 text-on-surface">No vehicles match this filter</h3>
                <p class="text-sm text-on-surface-variant mt-1">Try resetting the category or search terms.</p>
                <a href="<?= BASE_URL ?>/car-rentals.php" class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-primary text-white text-xs font-bold">
                    Reset Filters
                </a>
            </div>
        <?php endif; ?>

        <?php foreach ($fleet as $car): ?>
            <div class="rounded-3xl bg-surface-container-lowest border border-outline-variant/15 p-5 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <!-- Image -->
                    <a href="<?= BASE_URL ?>/vehicle.php?slug=<?= urlencode($car['slug']) ?>" class="relative w-full h-52 rounded-2xl bg-surface-container-low overflow-hidden flex items-center justify-center p-3 mb-4 block">
                        <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['name']) ?>" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-300"/>
                        <span class="absolute top-3 left-3 bg-surface-container-lowest/90 backdrop-blur-md px-2.5 py-1 rounded-full text-[11px] font-bold text-primary shadow-sm">
                            <?= htmlspecialchars($car['category']) ?>
                        </span>
                        <span class="absolute top-3 right-3 bg-emerald-50 text-emerald-800 border border-emerald-200 px-2 py-0.5 rounded-full text-[11px] font-bold flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">star</span> <?= $car['rating'] ?>
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

                    <!-- Features -->
                    <?php if (!empty($car['features'])): ?>
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <?php foreach (array_slice(explode(',', $car['features']), 0, 3) as $feat): ?>
                                <span class="px-2 py-0.5 rounded-lg bg-surface-container text-[11px] font-medium text-on-surface-variant">
                                    <?= htmlspecialchars(trim($feat)) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Inclusions -->
                    <div class="mt-4 pt-3 border-t border-outline-variant/10 text-xs text-on-surface-variant flex items-center justify-between">
                        <span>Includes <?= $car['free_km_per_day'] ?> km/day</span>
                        <span class="font-semibold text-emerald-700">₹0 Deposit</span>
                    </div>
                </div>

                <!-- Price & Instant Booking Trigger -->
                <div class="mt-6 pt-4 border-t border-outline-variant/15 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs text-on-surface-variant">Per Day Rate</div>
                        <div class="text-2xl font-black text-on-surface">
                            <?= format_rupee($car['price_per_day']) ?><span class="text-xs font-normal text-on-surface-variant">/day</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="<?= BASE_URL ?>/vehicle.php?slug=<?= urlencode($car['slug']) ?>" class="px-3 py-2.5 rounded-xl border border-outline-variant/30 hover:bg-surface-container text-on-surface text-xs font-bold transition-all" title="View vehicle specifications">
                            Details
                        </a>
                        <button type="button" onclick="openBookingModal(<?= htmlspecialchars(json_encode($car)) ?>)" class="px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold shadow-md transition-all active:scale-95 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">bolt</span>
                            <span>Book</span>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- BOOKING MODAL -->
<div id="booking-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-3xl max-w-xl w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 shadow-2xl border border-outline-variant/15 animate-fade-in">
        <div class="flex items-center justify-between pb-4 border-b border-outline-variant/15">
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-primary-fixed text-on-primary-fixed text-[10px] font-bold uppercase tracking-wider">Instant Confirmation</span>
                <h3 class="text-xl font-black text-on-surface mt-1" id="modal-car-name">Book Car Rental</h3>
            </div>
            <button type="button" onclick="closeBookingModal()" class="w-9 h-9 rounded-full bg-surface-container text-on-surface-variant hover:text-on-surface flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>

        <form action="<?= BASE_URL ?>/car-rentals.php" method="POST" class="mt-6 space-y-4">
            <input type="hidden" name="action" value="book_car"/>
            <input type="hidden" name="car_id" id="modal-car-id" value=""/>

            <!-- Rental Mode: Self-Drive vs Chauffeur -->
            <div>
                <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Rental Type</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 p-3 rounded-2xl border border-outline-variant/20 bg-surface-container-low cursor-pointer">
                        <input type="radio" name="rental_type" value="Self-Drive" checked class="text-primary"/>
                        <span class="text-xs font-bold text-on-surface">Self-Drive (You Drive)</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-2xl border border-outline-variant/20 bg-surface-container-low cursor-pointer">
                        <input type="radio" name="rental_type" value="With Chauffeur" class="text-primary"/>
                        <span class="text-xs font-bold text-on-surface">With Chauffeur (+₹600/day)</span>
                    </label>
                </div>
            </div>

            <!-- Customer Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Full Name *</label>
                    <input type="text" name="customer_name" required placeholder="e.g. Vikas Sharma" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Phone Number (WhatsApp) *</label>
                    <input type="tel" name="customer_phone" required placeholder="e.g. 9812345678" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none focus:border-primary"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Email Address</label>
                    <input type="email" name="customer_email" placeholder="vikas@gmail.com" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">City</label>
                    <input type="text" name="customer_city" value="Jind" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none focus:border-primary"/>
                </div>
            </div>

            <!-- Dates & Times -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Pickup Date &amp; Time</label>
                    <div class="flex gap-2">
                        <input type="date" name="pickup_date" id="modal-pickup-date" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>" min="<?= date('Y-m-d') ?>" class="w-2/3 px-3 py-2 rounded-xl bg-surface-container-low border border-outline-variant/20 text-xs font-semibold"/>
                        <input type="time" name="pickup_time" required value="09:00" class="w-1/3 px-2 py-2 rounded-xl bg-surface-container-low border border-outline-variant/20 text-xs font-semibold"/>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Return Date &amp; Time</label>
                    <div class="flex gap-2">
                        <input type="date" name="return_date" id="modal-return-date" required value="<?= date('Y-m-d', strtotime('+2 days')) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" class="w-2/3 px-3 py-2 rounded-xl bg-surface-container-low border border-outline-variant/20 text-xs font-semibold"/>
                        <input type="time" name="return_time" required value="20:00" class="w-1/3 px-2 py-2 rounded-xl bg-surface-container-low border border-outline-variant/20 text-xs font-semibold"/>
                    </div>
                </div>
            </div>

            <!-- Locations -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Pickup Location</label>
                    <select name="pickup_location" class="w-full px-3 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-xs font-semibold text-on-surface">
                        <option value="Jind Central Mobility Hub (Railway Station Rd)">Jind Central Mobility Hub (Railway Rd)</option>
                        <option value="Safidon Road Depot, Jind">Safidon Road Depot, Jind</option>
                        <option value="Doorstep Delivery in Jind (+₹250)">Doorstep Delivery in Jind (+₹250)</option>
                        <option value="Narwana Bus Stand Desk">Narwana Bus Stand Desk</option>
                        <option value="Rohtak Express Desk">Rohtak Express Desk</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Drop Location</label>
                    <select name="drop_location" class="w-full px-3 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-xs font-semibold text-on-surface">
                        <option value="Same as Pickup">Same as Pickup Hub</option>
                        <option value="Delhi Airport T3 Drop (+₹1800)">Delhi Airport T3 Drop (+₹1800)</option>
                        <option value="Chandigarh Airport Drop (+₹1800)">Chandigarh Airport Drop (+₹1800)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Special Requests (Optional)</label>
                <textarea name="special_requests" rows="2" placeholder="Destination (e.g. Manali trip, outstation wedding), extra fastag balance, child seat, etc." class="w-full px-3.5 py-2 rounded-xl bg-surface-container-low border border-outline-variant/20 text-xs font-semibold text-on-surface focus:outline-none focus:border-primary"></textarea>
            </div>

            <div class="p-4 rounded-2xl bg-surface-container text-xs text-on-surface-variant flex items-center justify-between">
                <span>Security Deposit: <strong class="text-emerald-700 font-bold">₹0 (Zero Deposit)</strong></span>
                <span>Payment: <strong class="text-on-surface font-bold">Pay at Pickup</strong></span>
            </div>

            <button type="submit" class="w-full py-4 rounded-2xl bg-primary hover:bg-primary-container text-white font-extrabold text-sm shadow-xl transition-all active:scale-95 flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">check_circle</span>
                <span>Confirm &amp; Reserve Car</span>
            </button>
        </form>
    </div>
</div>

<script>
function openBookingModal(car) {
    document.getElementById('modal-car-id').value = car.id;
    document.getElementById('modal-car-name').innerText = 'Book ' + car.name;
    document.getElementById('booking-modal').classList.remove('hidden');
}

function closeBookingModal() {
    document.getElementById('booking-modal').classList.add('hidden');
}

<?php if ($preselectedCarId > 0): ?>
document.addEventListener('DOMContentLoaded', () => {
    const fleetCars = <?= json_encode($fleet) ?>;
    const found = fleetCars.find(c => c.id == <?= $preselectedCarId ?>);
    if (found) {
        openBookingModal(found);
    }
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

