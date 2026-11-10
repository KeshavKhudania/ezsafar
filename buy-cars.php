<?php
$pageTitle = "Buy Certified Used Cars in Jind | 140-Point Inspected - EzSafar";
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// Handle Test Drive / Inspection Inquiry submission
$inquirySuccess = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_test_drive') {
    $carId = (int)($_POST['car_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $prefDate = sanitize($_POST['preferred_date'] ?? date('Y-m-d', strtotime('+1 day')));
    $message = sanitize($_POST['message'] ?? '');

    // Get car info
    $stmtC = $db->prepare("SELECT * FROM used_cars WHERE id = ?");
    $stmtC->execute([$carId]);
    $car = $stmtC->fetch();

    if ($car && !empty($name) && !empty($phone)) {
        $stmtIns = $db->prepare("INSERT INTO inquiries (inquiry_type, car_id, name, phone, email, preferred_date, message, status)
            VALUES ('Test Drive', ?, ?, ?, ?, ?, ?, 'New')");
        $stmtIns->execute([$carId, $name, $phone, $email, $prefDate, $message]);

        $inquirySuccess = [
            'car_title' => $car['title'],
            'name' => $name,
            'phone' => $phone,
            'date' => $prefDate
        ];

        set_flash('success', "Test drive request booked for " . htmlspecialchars($car['title']) . "! Our Jind sales team will contact you to confirm the time slot.");
    } else {
        set_flash('error', "Please provide your name and phone number to schedule your test drive.");
    }
}

// Filters
$brandFilter = sanitize($_GET['brand'] ?? '');
$budgetFilter = sanitize($_GET['budget'] ?? '');
$bodyFilter = sanitize($_GET['body_type'] ?? '');
$fuelFilter = sanitize($_GET['fuel'] ?? '');
$transFilter = sanitize($_GET['transmission'] ?? '');
$search = sanitize($_GET['q'] ?? '');

$sql = "SELECT * FROM used_cars WHERE status = 'Available'";
$params = [];

if ($brandFilter) {
    $sql .= " AND brand = ?";
    $params[] = $brandFilter;
}
if ($bodyFilter) {
    $sql .= " AND body_type = ?";
    $params[] = $bodyFilter;
}
if ($fuelFilter) {
    $sql .= " AND fuel_type = ?";
    $params[] = $fuelFilter;
}
if ($transFilter) {
    $sql .= " AND transmission = ?";
    $params[] = $transFilter;
}
if ($budgetFilter) {
    if ($budgetFilter === 'under-5') {
        $sql .= " AND price < 500000";
    } elseif ($budgetFilter === '5-10') {
        $sql .= " AND price BETWEEN 500000 AND 1000000";
    } elseif ($budgetFilter === '10-15') {
        $sql .= " AND price BETWEEN 1000000 AND 1500000";
    } elseif ($budgetFilter === 'above-15') {
        $sql .= " AND price > 1500000";
    }
}
if ($search) {
    $sql .= " AND (title LIKE ? OR brand LIKE ? OR model LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY is_featured DESC, price ASC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();

$preselectedCarId = (int)($_GET['test_drive'] ?? 0);
?>

<div class="max-w-7xl mx-auto px-4 md:px-8 py-8 w-full">
    <!-- Test Drive Confirmation Alert -->
    <?php if ($inquirySuccess): ?>
        <div class="mb-8 p-6 sm:p-8 rounded-3xl bg-secondary-fixed/50 border border-secondary/30 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-secondary-container text-white flex items-center justify-center shrink-0 shadow-lg">
                    <span class="material-symbols-outlined text-3xl">airline_seat_recline_normal</span>
                </div>
                <div>
                    <span class="px-2.5 py-0.5 rounded-full bg-secondary-container text-white text-xs font-bold uppercase tracking-wider">Test Drive Scheduled</span>
                    <h2 class="text-2xl font-black text-on-surface mt-1"><?= htmlspecialchars($inquirySuccess['car_title']) ?></h2>
                    <p class="text-sm text-on-surface-variant mt-1">
                        Scheduled for: <strong><?= date('l, d F Y', strtotime($inquirySuccess['date'])) ?></strong> at Jind Central Hub.
                    </p>
                    <p class="text-xs text-on-surface-variant mt-1">
                        We will have the keys and 140-point inspection certificate ready for you!
                    </p>
                </div>
            </div>
            <a href="https://wa.me/<?= PHONE_WHATSAPP ?>?text=Hi%20EzSafar,%20I%20have%20scheduled%20a%20test%20drive%20for%20<?= urlencode($inquirySuccess['car_title']) ?>" target="_blank" rel="noopener" class="px-5 py-3 rounded-xl bg-secondary-container text-white font-bold text-xs shadow hover:bg-secondary transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">chat</span> WhatsApp Concierge
            </a>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-6 border-b border-outline-variant/15">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-fixed text-on-secondary-fixed text-xs font-bold mb-2">
                <span class="material-symbols-outlined text-sm">workspace_premium</span> 140-POINT CERTIFIED INVENTORY
            </div>
            <h1 class="text-3xl sm:text-5xl font-black text-on-surface tracking-tight">
                Buy Certified Used Cars in Jind, Haryana
            </h1>
            <p class="text-sm text-on-surface-variant mt-2 max-w-2xl">
                Explore quality tested, non-accidental pre-owned cars with guaranteed original odometers, 1-year comprehensive warranty, 7-day money-back guarantee, and free Haryana RC transfer.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-on-surface-variant">Verified Cars:</span>
            <span class="px-3 py-1 rounded-full bg-surface-container font-bold text-sm text-secondary-container"><?= count($cars) ?> in Stock</span>
        </div>
    </div>

    <!-- Filters Ecosystem -->
    <div class="my-6 p-4 rounded-3xl bg-surface-container-low border border-outline-variant/15 space-y-4">
        <form action="<?= BASE_URL ?>/buy-cars.php" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Brand -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-on-surface-variant mb-1">Brand</label>
                <select name="brand" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl bg-surface-container-lowest border border-outline-variant/20 text-xs font-semibold text-on-surface focus:outline-none">
                    <option value="">All Brands</option>
                    <option value="Maruti Suzuki" <?= ($brandFilter === 'Maruti Suzuki') ? 'selected' : '' ?>>Maruti Suzuki</option>
                    <option value="Hyundai" <?= ($brandFilter === 'Hyundai') ? 'selected' : '' ?>>Hyundai</option>
                    <option value="Mahindra" <?= ($brandFilter === 'Mahindra') ? 'selected' : '' ?>>Mahindra</option>
                    <option value="Honda" <?= ($brandFilter === 'Honda') ? 'selected' : '' ?>>Honda</option>
                </select>
            </div>

            <!-- Budget -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-on-surface-variant mb-1">Budget</label>
                <select name="budget" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl bg-surface-container-lowest border border-outline-variant/20 text-xs font-semibold text-on-surface focus:outline-none">
                    <option value="">All Budgets</option>
                    <option value="under-5" <?= ($budgetFilter === 'under-5') ? 'selected' : '' ?>>Under ₹ 5 Lakh</option>
                    <option value="5-10" <?= ($budgetFilter === '5-10') ? 'selected' : '' ?>>₹ 5L - ₹ 10 Lakh</option>
                    <option value="10-15" <?= ($budgetFilter === '10-15') ? 'selected' : '' ?>>₹ 10L - ₹ 15 Lakh</option>
                    <option value="above-15" <?= ($budgetFilter === 'above-15') ? 'selected' : '' ?>>Above ₹ 15 Lakh</option>
                </select>
            </div>

            <!-- Body Type -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-on-surface-variant mb-1">Body Type</label>
                <select name="body_type" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl bg-surface-container-lowest border border-outline-variant/20 text-xs font-semibold text-on-surface focus:outline-none">
                    <option value="">All Body Types</option>
                    <option value="Hatchback" <?= ($bodyFilter === 'Hatchback') ? 'selected' : '' ?>>Hatchback</option>
                    <option value="Sedan" <?= ($bodyFilter === 'Sedan') ? 'selected' : '' ?>>Sedan</option>
                    <option value="SUV" <?= ($bodyFilter === 'SUV') ? 'selected' : '' ?>>SUV</option>
                </select>
            </div>

            <!-- Fuel -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-on-surface-variant mb-1">Fuel</label>
                <select name="fuel" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl bg-surface-container-lowest border border-outline-variant/20 text-xs font-semibold text-on-surface focus:outline-none">
                    <option value="">All Fuels</option>
                    <option value="Petrol" <?= ($fuelFilter === 'Petrol') ? 'selected' : '' ?>>Petrol</option>
                    <option value="Diesel" <?= ($fuelFilter === 'Diesel') ? 'selected' : '' ?>>Diesel</option>
                    <option value="CNG" <?= ($fuelFilter === 'CNG') ? 'selected' : '' ?>>CNG</option>
                </select>
            </div>

            <!-- Search & Reset -->
            <div class="flex items-end gap-2">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search Swift, Creta..." class="w-full px-3 py-2 rounded-xl bg-surface-container-lowest border border-outline-variant/20 text-xs font-semibold text-on-surface focus:outline-none"/>
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-secondary-container hover:bg-secondary text-white text-xs font-bold shadow">
                    <span class="material-symbols-outlined text-sm">search</span>
                </button>
                <a href="<?= BASE_URL ?>/buy-cars.php" class="px-3 py-2 rounded-xl bg-surface-container hover:bg-surface-container-high text-xs font-bold text-on-surface-variant" title="Reset Filters">
                    <span class="material-symbols-outlined text-sm">restart_alt</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Cars Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (empty($cars)): ?>
            <div class="col-span-full py-16 text-center">
                <span class="material-symbols-outlined text-5xl text-outline">car_crash</span>
                <h3 class="text-xl font-bold mt-2 text-on-surface">No vehicles match your criteria</h3>
                <p class="text-sm text-on-surface-variant mt-1">Try adjusting the price range or body type filter.</p>
                <a href="<?= BASE_URL ?>/buy-cars.php" class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-secondary-container text-white text-xs font-bold">
                    View All Cars
                </a>
            </div>
        <?php endif; ?>

        <?php foreach ($cars as $car): ?>
            <div class="rounded-3xl bg-surface-container-lowest border border-outline-variant/15 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <!-- Image Container -->
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

                    <!-- Details -->
                    <div class="p-5">
                        <div class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            <?= $car['year'] ?> • <?= htmlspecialchars($car['ownership']) ?> • <?= htmlspecialchars($car['color']) ?>
                        </div>
                        <h3 class="text-lg font-bold text-on-surface mt-1 group-hover:text-primary transition-colors">
                            <a href="<?= BASE_URL ?>/vehicle.php?slug=<?= urlencode($car['slug']) ?>">
                                <?= htmlspecialchars($car['title']) ?>
                            </a>
                        </h3>

                        <!-- Specs Row -->
                        <div class="grid grid-cols-3 gap-2 mt-4 p-3 rounded-2xl bg-surface-container-low text-center text-xs">
                            <div>
                                <span class="text-on-surface-variant block text-[10px]">KMs Driven</span>
                                <span class="font-bold text-on-surface"><?= number_format($car['kms_driven']) ?> km</span>
                            </div>
                            <div class="border-x border-outline-variant/20">
                                <span class="text-on-surface-variant block text-[10px]">Fuel</span>
                                <span class="font-bold text-on-surface"><?= htmlspecialchars($car['fuel_type']) ?></span>
                            </div>
                            <div>
                                <span class="text-on-surface-variant block text-[10px]">Transmission</span>
                                <span class="font-bold text-on-surface"><?= htmlspecialchars($car['transmission']) ?></span>
                            </div>
                        </div>

                        <!-- Highlights Tags -->
                        <?php if (!empty($car['highlights'])): ?>
                            <div class="mt-4 flex flex-wrap gap-1.5">
                                <?php foreach (explode(',', $car['highlights']) as $hl): ?>
                                    <span class="px-2 py-0.5 rounded-lg bg-surface-container text-[10px] font-semibold text-on-surface-variant flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[12px] text-emerald-600">check_circle</span>
                                        <?= htmlspecialchars(trim($hl)) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
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
                            <button type="button" onclick="openTestDriveModal(<?= htmlspecialchars(json_encode($car)) ?>)" class="px-4 py-2.5 rounded-xl bg-secondary-container hover:bg-secondary text-white text-xs font-bold shadow transition-all active:scale-95 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">airline_seat_recline_normal</span>
                                <span>Book Test Drive</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- TEST DRIVE BOOKING MODAL -->
<div id="test-drive-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-outline-variant/15 animate-fade-in">
        <div class="flex items-center justify-between pb-4 border-b border-outline-variant/15">
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-secondary-fixed text-on-secondary-fixed text-[10px] font-bold uppercase tracking-wider">Free Doorstep or Hub Test Drive</span>
                <h3 class="text-xl font-black text-on-surface mt-1" id="td-car-title">Schedule Test Drive</h3>
            </div>
            <button type="button" onclick="closeTestDriveModal()" class="w-9 h-9 rounded-full bg-surface-container text-on-surface-variant hover:text-on-surface flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>

        <form action="<?= BASE_URL ?>/buy-cars.php" method="POST" class="mt-6 space-y-4">
            <input type="hidden" name="action" value="book_test_drive"/>
            <input type="hidden" name="car_id" id="td-car-id" value=""/>

            <div>
                <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Your Full Name *</label>
                <input type="text" name="name" required placeholder="e.g. Ramesh Mor" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none focus:border-primary"/>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Phone (WhatsApp) *</label>
                    <input type="tel" name="phone" required placeholder="e.g. 9812981298" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Email (Optional)</label>
                    <input type="email" name="email" placeholder="ramesh@gmail.com" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none focus:border-primary"/>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Preferred Test Drive Date</label>
                <input type="date" name="preferred_date" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>" min="<?= date('Y-m-d') ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none focus:border-primary"/>
            </div>

            <div>
                <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Message or Questions</label>
                <textarea name="message" rows="2" placeholder="Tell us if you prefer testing at Jind Central Hub or doorstep in Jind..." class="w-full px-3.5 py-2 rounded-xl bg-surface-container-low border border-outline-variant/20 text-xs font-semibold text-on-surface focus:outline-none focus:border-primary"></textarea>
            </div>

            <div class="p-3 rounded-2xl bg-surface-container text-xs text-on-surface-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-base">verified</span>
                <span>Includes complete 140-point inspection report printout and loan eligibility check.</span>
            </div>

            <button type="submit" class="w-full py-4 rounded-2xl bg-secondary-container hover:bg-secondary text-white font-extrabold text-sm shadow-xl transition-all active:scale-95 flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">event_available</span>
                <span>Confirm Test Drive Slot</span>
            </button>
        </form>
    </div>
</div>

<script>
function openTestDriveModal(car) {
    document.getElementById('td-car-id').value = car.id;
    document.getElementById('td-car-title').innerText = 'Test Drive ' + car.title;
    document.getElementById('test-drive-modal').classList.remove('hidden');
}

function closeTestDriveModal() {
    document.getElementById('test-drive-modal').classList.add('hidden');
}

<?php if ($preselectedCarId > 0): ?>
document.addEventListener('DOMContentLoaded', () => {
    const usedCarsList = <?= json_encode($cars) ?>;
    const found = usedCarsList.find(c => c.id == <?= $preselectedCarId ?>);
    if (found) {
        openTestDriveModal(found);
    }
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

