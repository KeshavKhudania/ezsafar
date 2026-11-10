<?php
require_once __DIR__ . '/auth-check.php';
$db = get_db();

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'add') {
        $name = sanitize($_POST['name'] ?? '');
        $category = sanitize($_POST['category'] ?? 'Hatchback');
        $fuel = sanitize($_POST['fuel_type'] ?? 'Petrol');
        $transmission = sanitize($_POST['transmission'] ?? 'Manual');
        $seats = (int)($_POST['seats'] ?? 5);
        $price = (float)($_POST['price_per_day'] ?? 1500);
        $deposit = (float)($_POST['security_deposit'] ?? 0);
        $freeKm = (int)($_POST['free_km_per_day'] ?? 250);
        $extraKm = (float)($_POST['extra_km_charge'] ?? 12);
        $img = sanitize($_POST['image_url'] ?? '');
        $features = sanitize($_POST['features'] ?? '');
        $hub = sanitize($_POST['location_hub'] ?? 'Jind Central Mobility Hub');

        // SEO Fields
        $slug = slugify(sanitize($_POST['slug'] ?? ''));
        if (empty($slug)) {
            $slug = slugify($name);
        }
        // Ensure slug uniqueness
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM rental_cars WHERE slug = ?");
        $checkStmt->execute([$slug]);
        if ($checkStmt->fetchColumn() > 0) {
            $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
        }

        $metaTitle = sanitize($_POST['meta_title'] ?? '');
        if (empty($metaTitle)) {
            $metaTitle = "$name on Rent in Jind | EzSafar Car Rentals";
        }
        $metaDesc = sanitize($_POST['meta_description'] ?? '');
        if (empty($metaDesc)) {
            $metaDesc = "Rent $name in Jind, Haryana. Self-drive & chauffeur rental starting at ₹$price/day with zero deposit & doorstep delivery by EzSafar.";
        }
        $metaKeywords = sanitize($_POST['meta_keywords'] ?? '');
        if (empty($metaKeywords)) {
            $metaKeywords = "$name rent jind, self drive car rental jind, car hire haryana, ezsafar rentals";
        }

        if (!empty($name) && !empty($img) && $price > 0) {
            $stmt = $db->prepare("INSERT INTO rental_cars (name, slug, category, fuel_type, transmission, seats, price_per_day, security_deposit, free_km_per_day, extra_km_charge, image_url, features, location_hub, meta_title, meta_description, meta_keywords, is_available)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$name, $slug, $category, $fuel, $transmission, $seats, $price, $deposit, $freeKm, $extraKm, $img, $features, $hub, $metaTitle, $metaDesc, $metaKeywords]);
            set_flash('success', "New vehicle '$name' added to the rental fleet successfully with SEO slug '$slug'.");
        } else {
            set_flash('error', "Car name, image URL, and price per day are required.");
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $category = sanitize($_POST['category'] ?? 'Hatchback');
        $fuel = sanitize($_POST['fuel_type'] ?? 'Petrol');
        $transmission = sanitize($_POST['transmission'] ?? 'Manual');
        $seats = (int)($_POST['seats'] ?? 5);
        $price = (float)($_POST['price_per_day'] ?? 1500);
        $deposit = (float)($_POST['security_deposit'] ?? 0);
        $freeKm = (int)($_POST['free_km_per_day'] ?? 250);
        $extraKm = (float)($_POST['extra_km_charge'] ?? 12);
        $img = sanitize($_POST['image_url'] ?? '');
        $features = sanitize($_POST['features'] ?? '');
        $hub = sanitize($_POST['location_hub'] ?? 'Jind Central Mobility Hub');
        $rating = (float)($_POST['rating'] ?? 4.9);
        $trips = (int)($_POST['trips_count'] ?? 100);
        $avail = isset($_POST['is_available']) ? 1 : 0;

        // SEO Fields
        $slug = slugify(sanitize($_POST['slug'] ?? ''));
        if (empty($slug)) {
            $slug = slugify($name);
        }
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM rental_cars WHERE slug = ? AND id != ?");
        $checkStmt->execute([$slug, $id]);
        if ($checkStmt->fetchColumn() > 0) {
            $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
        }

        $metaTitle = sanitize($_POST['meta_title'] ?? '');
        $metaDesc = sanitize($_POST['meta_description'] ?? '');
        $metaKeywords = sanitize($_POST['meta_keywords'] ?? '');

        if ($id > 0 && !empty($name) && !empty($img) && $price > 0) {
            $stmt = $db->prepare("UPDATE rental_cars SET 
                name=?, slug=?, category=?, fuel_type=?, transmission=?, seats=?, price_per_day=?, 
                security_deposit=?, free_km_per_day=?, extra_km_charge=?, image_url=?, 
                features=?, location_hub=?, rating=?, trips_count=?, is_available=?,
                meta_title=?, meta_description=?, meta_keywords=?
                WHERE id=?");
            $stmt->execute([
                $name, $slug, $category, $fuel, $transmission, $seats, $price, 
                $deposit, $freeKm, $extraKm, $img, $features, $hub, $rating, 
                $trips, $avail, $metaTitle, $metaDesc, $metaKeywords, $id
            ]);
            set_flash('success', "Vehicle '$name' (ID #$id) updated successfully with SEO settings.");
        } else {
            set_flash('error', "Failed to update: Required fields are missing.");
        }
    } elseif ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        $current = (int)($_POST['current_status'] ?? 0);
        $new = $current ? 0 : 1;
        $stmt = $db->prepare("UPDATE rental_cars SET is_available = ? WHERE id = ?");
        $stmt->execute([$new, $id]);
        set_flash('info', "Vehicle availability toggled.");
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM rental_cars WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('warning', "Vehicle deleted from the fleet.");
    }

    header('Location: ' . BASE_URL . '/admin/rental-cars.php');
    exit;
}

$adminTitle = "Rental Fleet Management";
require_once __DIR__ . '/includes/admin-header.php';

$cars = $db->query("SELECT * FROM rental_cars ORDER BY id DESC")->fetchAll();
?>

<!-- Header & Add Button -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-xl font-bold text-slate-800">Rental Cars (<?= count($cars) ?> Total)</h3>
        <p class="text-xs text-slate-500">Manage, edit, and update cars available for self-drive and chauffeur rentals.</p>
    </div>
    <button onclick="document.getElementById('add-modal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold shadow flex items-center gap-1.5 self-start">
        <span class="material-symbols-outlined text-base">add</span>
        <span>Add New Rental Car</span>
    </button>
</div>

<!-- Cars Table -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-200">
                <tr>
                    <th class="p-4">Car</th>
                    <th class="p-4">Category &amp; Specs</th>
                    <th class="p-4">Pricing</th>
                    <th class="p-4">Hub Location</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($cars as $car): ?>
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="" class="w-16 h-12 object-contain rounded-lg bg-slate-100 p-1"/>
                                <div>
                                    <div class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($car['name']) ?></div>
                                    <div class="text-[11px] text-slate-400">ID: #<?= $car['id'] ?> • <?= $car['trips_count'] ?> trips • <?= $car['rating'] ?> ★</div>
                                    <?php if (!empty($car['slug'])): ?>
                                        <a href="../vehicle.php?slug=<?= urlencode($car['slug']) ?>" target="_blank" class="text-primary hover:underline text-[10px] font-semibold inline-flex items-center gap-0.5 mt-0.5">
                                            <span>/vehicle/<?= htmlspecialchars($car['slug']) ?></span>
                                            <span class="material-symbols-outlined text-[11px]">open_in_new</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px]"><?= $car['category'] ?></span>
                            <div class="mt-1 text-slate-500 font-medium">
                                <?= $car['fuel_type'] ?> • <?= $car['transmission'] ?> • <?= $car['seats'] ?> Seats
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="font-extrabold text-slate-800 text-sm"><?= format_rupee($car['price_per_day']) ?>/day</div>
                            <div class="text-[11px] text-slate-400">Free <?= $car['free_km_per_day'] ?> km/day • ₹<?= $car['security_deposit'] ?> dep</div>
                        </td>
                        <td class="p-4 text-slate-600 font-medium">
                            <?= htmlspecialchars($car['location_hub']) ?>
                        </td>
                        <td class="p-4">
                            <form action="" method="POST" class="inline">
                                <input type="hidden" name="action" value="toggle_status"/>
                                <input type="hidden" name="id" value="<?= $car['id'] ?>"/>
                                <input type="hidden" name="current_status" value="<?= $car['is_available'] ?>"/>
                                <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold cursor-pointer transition-all <?= $car['is_available'] ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200' ?>">
                                    <?= $car['is_available'] ? '● Available' : '○ Disabled' ?>
                                </button>
                            </form>
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" onclick="openEditCarModal(<?= htmlspecialchars(json_encode($car)) ?>)" class="p-1.5 rounded-lg text-slate-500 hover:text-primary hover:bg-slate-100" title="Edit Car Data">
                                    <span class="material-symbols-outlined text-base">edit</span>
                                </button>
                                <form action="" method="POST" onsubmit="return confirm('Delete this vehicle from the fleet?')" class="inline">
                                    <input type="hidden" name="action" value="delete"/>
                                    <input type="hidden" name="id" value="<?= $car['id'] ?>"/>
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Delete Car">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- EDIT CAR MODAL -->
<div id="edit-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 shadow-2xl border border-slate-200 animate-fade-in">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-bold uppercase tracking-wider">Fleet Editor</span>
                <h3 class="text-lg font-bold text-slate-800 mt-1" id="edit-title">Edit Rental Car</h3>
            </div>
            <button onclick="document.getElementById('edit-modal').classList.add('hidden')" class="p-1 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="" method="POST" class="mt-5 space-y-4 text-xs">
            <input type="hidden" name="action" value="edit"/>
            <input type="hidden" name="id" id="edit-id" value=""/>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Car Name &amp; Model *</label>
                <input type="text" name="name" id="edit-name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Category</label>
                    <select name="category" id="edit-category" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none">
                        <option value="Hatchback">Hatchback</option>
                        <option value="Sedan">Sedan</option>
                        <option value="Compact SUV">Compact SUV</option>
                        <option value="Luxury SUV">Luxury SUV</option>
                        <option value="MUV / 7-Seater">MUV / 7-Seater</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Fuel Type</label>
                    <select name="fuel_type" id="edit-fuel" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none">
                        <option value="Petrol">Petrol</option>
                        <option value="Diesel">Diesel</option>
                        <option value="CNG">CNG</option>
                        <option value="Electric">Electric</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Transmission</label>
                    <select name="transmission" id="edit-transmission" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none">
                        <option value="Manual">Manual</option>
                        <option value="Automatic">Automatic</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Seats</label>
                    <input type="number" name="seats" id="edit-seats" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Price / Day (₹) *</label>
                    <input type="number" name="price_per_day" id="edit-price" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Deposit (₹)</label>
                    <input type="number" name="security_deposit" id="edit-deposit" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Free KM/Day</label>
                    <input type="number" name="free_km_per_day" id="edit-free-km" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Extra KM Charge (₹/km)</label>
                    <input type="number" step="0.5" name="extra_km_charge" id="edit-extra-km" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Rating (★)</label>
                    <input type="number" step="0.05" min="1" max="5" name="rating" id="edit-rating" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Completed Trips</label>
                    <input type="number" name="trips_count" id="edit-trips" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Location Hub</label>
                <input type="text" name="location_hub" id="edit-hub" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none"/>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Image URL *</label>
                <input type="url" name="image_url" id="edit-img" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-mono text-slate-800 focus:outline-none focus:border-primary"/>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Features (Comma separated)</label>
                <input type="text" name="features" id="edit-features" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none"/>
            </div>

            <!-- SEO Optimization Suite -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="flex items-center gap-2 text-slate-800 font-bold">
                    <span class="material-symbols-outlined text-primary text-base">travel_explore</span>
                    <span>Search Engine Optimization (SEO) &amp; OpenGraph</span>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">SEO URL Slug</label>
                    <input type="text" name="slug" id="edit-slug" placeholder="e.g. maruti-suzuki-fronx-turbo-2024" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono text-slate-800 focus:outline-none focus:border-primary"/>
                    <p class="text-[10px] text-slate-400 mt-0.5">Used in public vehicle URL: ezsafar.com/vehicle.php?slug=...</p>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Custom Meta Title</label>
                    <input type="text" name="meta_title" id="edit-meta-title" placeholder="Leave blank for auto-generated title" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Custom Meta Description</label>
                    <textarea name="meta_description" id="edit-meta-desc" rows="2" placeholder="Leave blank for auto-generated description (recommended 140-160 chars)" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary"></textarea>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Meta Keywords</label>
                    <input type="text" name="meta_keywords" id="edit-meta-keywords" placeholder="car rent jind, self drive car, car hire haryana" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
                </div>
            </div>

            <div class="flex items-center gap-2 p-3 rounded-xl bg-slate-50 border border-slate-200">
                <input type="checkbox" name="is_available" id="edit-avail" value="1" class="w-4 h-4 text-primary rounded"/>
                <label for="edit-avail" class="font-bold text-slate-700 cursor-pointer">Available for Customer Bookings</label>
            </div>

            <div class="pt-3 flex justify-end gap-3 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-bold hover:bg-slate-50">
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

<!-- Add Modal -->
<div id="add-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 shadow-2xl border border-slate-200 animate-fade-in">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <h3 class="text-lg font-bold text-slate-800">Add New Rental Car</h3>
            <button onclick="document.getElementById('add-modal').classList.add('hidden')" class="p-1 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="" method="POST" class="mt-5 space-y-4 text-xs">
            <input type="hidden" name="action" value="add"/>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Car Name &amp; Model *</label>
                <input type="text" name="name" required placeholder="e.g. Maruti Suzuki Fronx Turbo 2024" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Category</label>
                    <select name="category" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none">
                        <option value="Hatchback">Hatchback</option>
                        <option value="Sedan">Sedan</option>
                        <option value="Compact SUV">Compact SUV</option>
                        <option value="Luxury SUV">Luxury SUV</option>
                        <option value="MUV / 7-Seater">MUV / 7-Seater</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Fuel Type</label>
                    <select name="fuel_type" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none">
                        <option value="Petrol">Petrol</option>
                        <option value="Diesel">Diesel</option>
                        <option value="CNG">CNG</option>
                        <option value="Electric">Electric</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Transmission</label>
                    <select name="transmission" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none">
                        <option value="Manual">Manual</option>
                        <option value="Automatic">Automatic</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Seats</label>
                    <input type="number" name="seats" value="5" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Price / Day (₹) *</label>
                    <input type="number" name="price_per_day" value="1800" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Deposit (₹)</label>
                    <input type="number" name="security_deposit" value="0" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Free KM/Day</label>
                    <input type="number" name="free_km_per_day" value="250" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Image URL *</label>
                <input type="url" name="image_url" required placeholder="https://..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-mono text-slate-800 focus:outline-none focus:border-primary"/>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Features (Comma separated)</label>
                <input type="text" name="features" value="Touchscreen Infotainment, Bluetooth, Chilled AC, Fastag, Airbags" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none"/>
            </div>

            <!-- SEO Optimization Suite -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="flex items-center gap-2 text-slate-800 font-bold">
                    <span class="material-symbols-outlined text-primary text-base">travel_explore</span>
                    <span>Search Engine Optimization (SEO) &amp; OpenGraph</span>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">SEO URL Slug (Optional)</label>
                    <input type="text" name="slug" placeholder="e.g. maruti-suzuki-fronx-turbo-2024 (auto-generated if empty)" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono text-slate-800 focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Custom Meta Title</label>
                    <input type="text" name="meta_title" placeholder="Leave blank for auto-optimized title" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Custom Meta Description</label>
                    <textarea name="meta_description" rows="2" placeholder="Leave blank for auto-optimized description (140-160 chars)" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary"></textarea>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Meta Keywords</label>
                    <input type="text" name="meta_keywords" placeholder="car rent jind, self drive car, car hire haryana" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
                </div>
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('add-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-bold hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white font-bold shadow">
                    Save Vehicle
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditCarModal(car) {
    document.getElementById('edit-id').value = car.id;
    document.getElementById('edit-title').innerText = 'Edit ' + car.name;
    document.getElementById('edit-name').value = car.name;
    document.getElementById('edit-category').value = car.category;
    document.getElementById('edit-fuel').value = car.fuel_type;
    document.getElementById('edit-transmission').value = car.transmission;
    document.getElementById('edit-seats').value = car.seats;
    document.getElementById('edit-price').value = car.price_per_day;
    document.getElementById('edit-deposit').value = car.security_deposit;
    document.getElementById('edit-free-km').value = car.free_km_per_day;
    document.getElementById('edit-extra-km').value = car.extra_km_charge || 12;
    document.getElementById('edit-rating').value = car.rating || 4.9;
    document.getElementById('edit-trips').value = car.trips_count || 0;
    document.getElementById('edit-hub').value = car.location_hub || '';
    document.getElementById('edit-img').value = car.image_url;
    document.getElementById('edit-features').value = car.features || '';
    document.getElementById('edit-slug').value = car.slug || '';
    document.getElementById('edit-meta-title').value = car.meta_title || '';
    document.getElementById('edit-meta-desc').value = car.meta_description || '';
    document.getElementById('edit-meta-keywords').value = car.meta_keywords || '';
    document.getElementById('edit-avail').checked = (parseInt(car.is_available) === 1);

    document.getElementById('edit-modal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
