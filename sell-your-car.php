<?php
$pageTitle = "Sell Your Car Online in Jind | Best Price & Instant Payment - EzSafar";
require_once __DIR__ . '/includes/header.php';

$db = get_db();

$sellSuccess = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_sell_car') {
    $regNumber = strtoupper(sanitize($_POST['reg_number'] ?? ''));
    $brand = sanitize($_POST['brand'] ?? '');
    $model = sanitize($_POST['model'] ?? '');
    $year = (int)($_POST['year'] ?? date('Y'));
    $fuelType = sanitize($_POST['fuel_type'] ?? 'Petrol');
    $transmission = sanitize($_POST['transmission'] ?? 'Manual');
    $kmsDriven = (int)($_POST['kms_driven'] ?? 30000);
    $condition = sanitize($_POST['car_condition'] ?? 'Good');
    $expectedPrice = (float)($_POST['expected_price'] ?? 0);
    $estMin = (float)($_POST['estimated_range_min'] ?? 0);
    $estMax = (float)($_POST['estimated_range_max'] ?? 0);
    $sellerName = sanitize($_POST['seller_name'] ?? '');
    $sellerPhone = sanitize($_POST['seller_phone'] ?? '');
    $sellerCity = sanitize($_POST['seller_city'] ?? 'Jind');
    $prefDate = sanitize($_POST['inspection_pref_date'] ?? date('Y-m-d', strtotime('+1 day')));

    if (!empty($regNumber) && !empty($sellerName) && !empty($sellerPhone)) {
        $reqNumber = generate_reference_id('EZS');

        $stmt = $db->prepare("INSERT INTO sell_requests 
            (request_number, reg_number, brand, model, year, fuel_type, transmission, kms_driven, car_condition, expected_price, estimated_range_min, estimated_range_max, seller_name, seller_phone, seller_city, inspection_pref_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending Review')");
        $stmt->execute([
            $reqNumber, $regNumber, $brand, $model, $year, $fuelType, $transmission, $kmsDriven,
            $condition, $expectedPrice, $estMin, $estMax, $sellerName, $sellerPhone, $sellerCity, $prefDate
        ]);

        $sellSuccess = [
            'request_number' => $reqNumber,
            'car' => "$year $brand $model ($regNumber)",
            'seller_name' => $sellerName,
            'seller_phone' => $sellerPhone,
            'est_range' => ($estMin > 0 && $estMax > 0) ? (format_rupee($estMin) . ' - ' . format_rupee($estMax)) : 'Custom Valuation',
            'date' => $prefDate
        ];

        set_flash('success', "Your car valuation request has been submitted! Reference ID: $reqNumber. Our Jind inspection specialist will call you shortly.");
    } else {
        set_flash('error', "Please fill in your vehicle registration number, name, and phone number.");
    }
}

$prefillReg = sanitize($_GET['reg'] ?? '');
?>

<div class="max-w-5xl mx-auto px-4 md:px-8 py-8 w-full">
    <!-- Success Alert -->
    <?php if ($sellSuccess): ?>
        <div class="mb-8 p-6 sm:p-8 rounded-3xl bg-emerald-50 border-2 border-emerald-300 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-lg">
                    <span class="material-symbols-outlined text-3xl">currency_rupee</span>
                </div>
                <div>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-200 text-emerald-900 text-xs font-bold uppercase tracking-wider">Evaluation Request Logged</span>
                    <h2 class="text-2xl font-black text-emerald-950 mt-1"><?= htmlspecialchars($sellSuccess['car']) ?></h2>
                    <p class="text-sm text-emerald-800 mt-1">
                        Reference ID: <strong class="text-emerald-950 font-mono"><?= $sellSuccess['request_number'] ?></strong> • 
                        Estimated Range: <strong><?= $sellSuccess['est_range'] ?></strong>
                    </p>
                    <p class="text-xs text-emerald-700 mt-1">
                        Doorstep inspection booked for <?= date('d M Y', strtotime($sellSuccess['date'])) ?> in Jind. Payment will be released immediately upon physical inspection.
                    </p>
                </div>
            </div>
            <a href="https://wa.me/<?= PHONE_WHATSAPP ?>?text=Hi%20EzSafar,%20I%20requested%20car%20valuation%20<?= urlencode($sellSuccess['request_number']) ?>%20for%20<?= urlencode($sellSuccess['car']) ?>" target="_blank" rel="noopener" class="px-5 py-3 rounded-xl bg-emerald-700 text-white font-bold text-xs shadow hover:bg-emerald-800 transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">chat</span> Expedite on WhatsApp
            </a>
        </div>
    <?php endif; ?>

    <!-- Header Section -->
    <div class="text-center max-w-3xl mx-auto mb-10">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-fixed text-on-secondary-fixed text-xs font-bold mb-3">
            <span class="material-symbols-outlined text-sm">payments</span> BEST PRICE GUARANTEE • PAYMENT IN 60 MINS
        </div>
        <h1 class="text-3xl sm:text-5xl font-black text-on-surface tracking-tight">
            Sell Your Car in Jind at the Right Market Price
        </h1>
        <p class="text-sm sm:text-base text-on-surface-variant mt-2">
            Instant valuation backed by live Haryana market transaction data. Free doorstep inspection at your home or shop in Jind, Narwana, and Rohtak.
        </p>
    </div>

    <!-- 3-Step Valuation Card -->
    <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-10 shadow-xl border border-outline-variant/15">
        <form action="<?= BASE_URL ?>/sell-your-car.php" method="POST" id="sell-car-form" onsubmit="calculateEstimatedPrice()" class="space-y-8">
            <input type="hidden" name="action" value="submit_sell_car"/>
            <input type="hidden" name="estimated_range_min" id="input_est_min" value="0"/>
            <input type="hidden" name="estimated_range_max" id="input_est_max" value="0"/>

            <!-- STEP 1: VEHICLE DETAILS -->
            <div>
                <div class="flex items-center gap-2 mb-4 pb-2 border-b border-outline-variant/15">
                    <span class="w-7 h-7 rounded-full bg-primary text-white text-xs font-black flex items-center justify-center">1</span>
                    <h3 class="text-lg font-bold text-on-surface">Vehicle Information</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Vehicle Reg Number *</label>
                        <input type="text" name="reg_number" required value="<?= htmlspecialchars($prefillReg) ?>" placeholder="HR-31-AB-1234" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-black uppercase text-on-surface focus:outline-none focus:border-primary"/>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Make / Brand *</label>
                        <select name="brand" id="brand-select" onchange="updateEstPriceUI()" required class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none">
                            <option value="Maruti Suzuki">Maruti Suzuki</option>
                            <option value="Hyundai">Hyundai</option>
                            <option value="Mahindra">Mahindra</option>
                            <option value="Tata">Tata Motors</option>
                            <option value="Honda">Honda</option>
                            <option value="Toyota">Toyota</option>
                            <option value="Kia">Kia</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Model &amp; Variant *</label>
                        <input type="text" name="model" required placeholder="e.g. Swift VXi / Creta SX" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none focus:border-primary"/>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Manufacturing Year</label>
                        <select name="year" id="year-select" onchange="updateEstPriceUI()" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none">
                            <?php for ($y = date('Y'); $y >= 2012; $y--): ?>
                                <option value="<?= $y ?>" <?= ($y === 2021) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Fuel Type</label>
                        <select name="fuel_type" id="fuel-select" onchange="updateEstPriceUI()" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none">
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="CNG">CNG</option>
                            <option value="Electric">Electric</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Transmission</label>
                        <select name="transmission" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none">
                            <option value="Manual">Manual</option>
                            <option value="Automatic">Automatic</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Kilometers Driven</label>
                        <input type="number" name="kms_driven" id="kms-input" value="35000" step="5000" oninput="updateEstPriceUI()" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none"/>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Car Condition</label>
                        <select name="car_condition" id="condition-select" onchange="updateEstPriceUI()" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none">
                            <option value="Excellent">Excellent (Original Paint, No scratches)</option>
                            <option value="Good" selected>Good (Minor wear, Normal city use)</option>
                            <option value="Fair">Fair (Needs bumper touchup or minor work)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Your Expected Price (₹)</label>
                        <input type="number" name="expected_price" placeholder="e.g. 550000" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none"/>
                    </div>
                </div>
            </div>

            <!-- DYNAMIC INSTANT ESTIMATE BANNER -->
            <div class="p-6 rounded-2xl bg-gradient-to-r from-surface-container-high via-surface-container to-surface-container-low border border-outline-variant/20 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xl shadow">
                        ₹
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Instant AI Valuation Range</div>
                        <div class="text-2xl sm:text-3xl font-black text-on-surface" id="dynamic-price-display">
                            ₹ 5,20,000 - ₹ 5,60,000
                        </div>
                    </div>
                </div>
                <div class="text-xs text-on-surface-variant max-w-xs text-right">
                    Guaranteed payment within 60 minutes of inspection. Zero documentation charges or dealer cut.
                </div>
            </div>

            <!-- STEP 2: SELLER CONTACT & FREE INSPECTION -->
            <div>
                <div class="flex items-center gap-2 mb-4 pb-2 border-b border-outline-variant/15">
                    <span class="w-7 h-7 rounded-full bg-primary text-white text-xs font-black flex items-center justify-center">2</span>
                    <h3 class="text-lg font-bold text-on-surface">Owner &amp; Free Doorstep Inspection Booking</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Owner Name *</label>
                        <input type="text" name="seller_name" required placeholder="e.g. Kuldeep Singh" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none focus:border-primary"/>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">WhatsApp / Phone Number *</label>
                        <input type="tel" name="seller_phone" required placeholder="e.g. 9416012345" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none focus:border-primary"/>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">City / Locality in Haryana</label>
                        <input type="text" name="seller_city" value="Jind (Urban Estate)" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none focus:border-primary"/>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Inspection Date</label>
                        <input type="date" name="inspection_pref_date" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>" min="<?= date('Y-m-d') ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/20 text-sm font-semibold text-on-surface focus:outline-none focus:border-primary"/>
                    </div>
                </div>
            </div>

            <!-- Submit CTA -->
            <div class="pt-4">
                <button type="submit" class="w-full py-4 rounded-2xl bg-secondary-container hover:bg-secondary text-white font-black text-base shadow-xl transition-all active:scale-95 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">payments</span>
                    <span>Submit &amp; Schedule Free Doorstep Inspection</span>
                </button>
                <p class="text-center text-xs text-on-surface-variant mt-3">
                    By submitting, you agree to receive instant quote updates via SMS &amp; WhatsApp from EzSafar Jind.
                </p>
            </div>
        </form>
    </div>
</div>

<script>
function calculateEstimatedPrice() {
    const year = parseInt(document.getElementById('year-select').value) || 2021;
    const kms = parseInt(document.getElementById('kms-input').value) || 35000;
    const brand = document.getElementById('brand-select').value;
    const condition = document.getElementById('condition-select').value;

    let base = 750000;
    if (brand === 'Maruti Suzuki') base = 680000;
    else if (brand === 'Hyundai') base = 780000;
    else if (brand === 'Mahindra') base = 950000;
    else if (brand === 'Toyota') base = 1100000;

    const age = Math.max(0, 2025 - year);
    let depreciated = base * Math.pow(0.89, age);
    depreciated -= (kms / 10000) * 12000;

    if (condition === 'Excellent') depreciated *= 1.06;
    if (condition === 'Fair') depreciated *= 0.92;

    const minPrice = Math.max(120000, Math.round(depreciated * 0.95 / 5000) * 5000);
    const maxPrice = Math.max(150000, Math.round(depreciated * 1.05 / 5000) * 5000);

    document.getElementById('input_est_min').value = minPrice;
    document.getElementById('input_est_max').value = maxPrice;
    return { minPrice, maxPrice };
}

function updateEstPriceUI() {
    const { minPrice, maxPrice } = calculateEstimatedPrice();
    const formatter = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 });
    document.getElementById('dynamic-price-display').innerText = 
        formatter.format(minPrice) + ' - ' + formatter.format(maxPrice);
}

document.addEventListener('DOMContentLoaded', updateEstPriceUI);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

