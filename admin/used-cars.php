<?php
require_once __DIR__ . '/auth-check.php';
$db = get_db();

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'add') {
        $title = sanitize($_POST['title'] ?? '');
        $brand = sanitize($_POST['brand'] ?? '');
        $model = sanitize($_POST['model'] ?? '');
        $variant = sanitize($_POST['variant'] ?? '');
        $year = (int)($_POST['year'] ?? 2022);
        $price = (float)($_POST['price'] ?? 500000);
        $origPrice = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
        $emi = (float)($_POST['emi_per_month'] ?? ($price * 0.02));
        $kms = (int)($_POST['kms_driven'] ?? 30000);
        $fuel = sanitize($_POST['fuel_type'] ?? 'Petrol');
        $trans = sanitize($_POST['transmission'] ?? 'Manual');
        $ownership = sanitize($_POST['ownership'] ?? '1st Owner');
        $regState = sanitize($_POST['registration_state'] ?? 'HR-31 (Jind)');
        $body = sanitize($_POST['body_type'] ?? 'Hatchback');
        $color = sanitize($_POST['color'] ?? 'White');
        $insurance = sanitize($_POST['insurance_validity'] ?? 'Comprehensive till Dec 2026');
        $inspection = sanitize($_POST['inspection_status'] ?? 'Passed 140-Pt Inspection');
        $img = sanitize($_POST['image_url'] ?? '');
        $desc = sanitize($_POST['description'] ?? '');
        $highlights = sanitize($_POST['highlights'] ?? '140-Point Inspected, Free RC Transfer, 1-Year Warranty');

        // SEO Fields
        $slug = slugify(sanitize($_POST['slug'] ?? ''));
        if (empty($slug)) {
            $slug = slugify($title);
        }
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM used_cars WHERE slug = ?");
        $checkStmt->execute([$slug]);
        if ($checkStmt->fetchColumn() > 0) {
            $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
        }

        $metaTitle = sanitize($_POST['meta_title'] ?? '');
        if (empty($metaTitle)) {
            $metaTitle = "$title for Sale in Jind | 140-Pt Inspected | EzSafar";
        }
        $metaDesc = sanitize($_POST['meta_description'] ?? '');
        if (empty($metaDesc)) {
            $metaDesc = "Buy certified pre-owned $title in Jind, Haryana. " . number_format($kms) . " km driven, $fuel, 140-point inspection passed, warranty & easy EMI available at EzSafar.";
        }
        $metaKeywords = sanitize($_POST['meta_keywords'] ?? '');
        if (empty($metaKeywords)) {
            $metaKeywords = "$brand $model, buy used car jind, second hand car haryana, certified pre-owned car";
        }

        if (!empty($title) && !empty($brand) && $price > 0 && !empty($img)) {
            $stmt = $db->prepare("INSERT INTO used_cars 
                (title, slug, brand, model, variant, year, price, original_price, emi_per_month, kms_driven, fuel_type, transmission, ownership, registration_state, body_type, color, insurance_validity, inspection_status, image_url, description, highlights, meta_title, meta_description, meta_keywords, status, is_featured)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Available', 1)");
            $stmt->execute([
                $title, $slug, $brand, $model, $variant, $year, $price, $origPrice, $emi, 
                $kms, $fuel, $trans, $ownership, $regState, $body, $color, $insurance, 
                $inspection, $img, $desc, $highlights, $metaTitle, $metaDesc, $metaKeywords
            ]);
            set_flash('success', "Used car '$title' added to inventory successfully with SEO slug '$slug'.");
        } else {
            set_flash('error', "Title, brand, price, and image URL are required.");
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $title = sanitize($_POST['title'] ?? '');
        $brand = sanitize($_POST['brand'] ?? '');
        $model = sanitize($_POST['model'] ?? '');
        $variant = sanitize($_POST['variant'] ?? '');
        $year = (int)($_POST['year'] ?? 2022);
        $price = (float)($_POST['price'] ?? 500000);
        $origPrice = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
        $emi = (float)($_POST['emi_per_month'] ?? ($price * 0.02));
        $kms = (int)($_POST['kms_driven'] ?? 30000);
        $fuel = sanitize($_POST['fuel_type'] ?? 'Petrol');
        $trans = sanitize($_POST['transmission'] ?? 'Manual');
        $ownership = sanitize($_POST['ownership'] ?? '1st Owner');
        $regState = sanitize($_POST['registration_state'] ?? 'HR-31 (Jind)');
        $body = sanitize($_POST['body_type'] ?? 'Hatchback');
        $color = sanitize($_POST['color'] ?? 'White');
        $insurance = sanitize($_POST['insurance_validity'] ?? '');
        $inspection = sanitize($_POST['inspection_status'] ?? '');
        $img = sanitize($_POST['image_url'] ?? '');
        $desc = sanitize($_POST['description'] ?? '');
        $highlights = sanitize($_POST['highlights'] ?? '');
        $status = sanitize($_POST['status'] ?? 'Available');
        $featured = isset($_POST['is_featured']) ? 1 : 0;

        // SEO Fields
        $slug = slugify(sanitize($_POST['slug'] ?? ''));
        if (empty($slug)) {
            $slug = slugify($title);
        }
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM used_cars WHERE slug = ? AND id != ?");
        $checkStmt->execute([$slug, $id]);
        if ($checkStmt->fetchColumn() > 0) {
            $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
        }

        $metaTitle = sanitize($_POST['meta_title'] ?? '');
        $metaDesc = sanitize($_POST['meta_description'] ?? '');
        $metaKeywords = sanitize($_POST['meta_keywords'] ?? '');

        if ($id > 0 && !empty($title) && $price > 0 && !empty($img)) {
            $stmt = $db->prepare("UPDATE used_cars SET 
                title=?, slug=?, brand=?, model=?, variant=?, year=?, price=?, original_price=?, emi_per_month=?, 
                kms_driven=?, fuel_type=?, transmission=?, ownership=?, registration_state=?, 
                body_type=?, color=?, insurance_validity=?, inspection_status=?, image_url=?, 
                description=?, highlights=?, meta_title=?, meta_description=?, meta_keywords=?,
                status=?, is_featured=?
                WHERE id=?");
            $stmt->execute([
                $title, $slug, $brand, $model, $variant, $year, $price, $origPrice, $emi,
                $kms, $fuel, $trans, $ownership, $regState, $body, $color, $insurance,
                $inspection, $img, $desc, $highlights, $metaTitle, $metaDesc, $metaKeywords,
                $status, $featured, $id
            ]);
            set_flash('success', "Used car '$title' (ID #$id) updated successfully with SEO settings.");
        } else {
            set_flash('error', "Failed to update: Required fields are missing.");
        }
    } elseif ($action === 'update_status') {
        $id = (int)($_POST['id'] ?? 0);
        $status = sanitize($_POST['status'] ?? 'Available');
        $stmt = $db->prepare("UPDATE used_cars SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        set_flash('info', "Vehicle status updated to $status.");
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM used_cars WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('warning', "Vehicle deleted from used car inventory.");
    }

    header('Location: ' . BASE_URL . '/admin/used-cars.php');
    exit;
}

$adminTitle = "Certified Used Cars Inventory";
require_once __DIR__ . '/includes/admin-header.php';

$cars = $db->query("SELECT * FROM used_cars ORDER BY id DESC")->fetchAll();
?>

<!-- Header & Add Button -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-xl font-bold text-slate-800">Certified Used Cars Inventory (<?= count($cars) ?> Total)</h3>
        <p class="text-xs text-slate-500">Manage, edit, and update 140-point inspected pre-owned cars for sale in Jind, Haryana.</p>
    </div>
    <button onclick="document.getElementById('add-used-modal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-secondary-container hover:bg-secondary text-white text-xs font-bold shadow flex items-center gap-1.5 self-start">
        <span class="material-symbols-outlined text-base">add</span>
        <span>Add Certified Used Car</span>
    </button>
</div>

<!-- Cars Table -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-200">
                <tr>
                    <th class="p-4">Car &amp; Photos</th>
                    <th class="p-4">Year &amp; Registration</th>
                    <th class="p-4">Specs &amp; KMs</th>
                    <th class="p-4">Price &amp; EMI</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($cars as $car): ?>
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="" class="w-16 h-12 object-cover rounded-lg bg-slate-100"/>
                                <div>
                                    <div class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($car['title']) ?></div>
                                    <div class="text-[11px] text-slate-400"><?= $car['brand'] ?> • <?= $car['body_type'] ?> • <?= $car['color'] ?></div>
                                    <?php if (!empty($car['slug'])): ?>
                                        <a href="../vehicle.php?slug=<?= urlencode($car['slug']) ?>" target="_blank" class="text-secondary-container hover:underline text-[10px] font-semibold inline-flex items-center gap-0.5 mt-0.5">
                                            <span>/vehicle/<?= htmlspecialchars($car['slug']) ?></span>
                                            <span class="material-symbols-outlined text-[11px]">open_in_new</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800"><?= $car['year'] ?> Model</div>
                            <div class="text-[11px] text-slate-500 font-mono"><?= htmlspecialchars($car['registration_state']) ?></div>
                        </td>
                        <td class="p-4">
                            <div class="font-semibold text-slate-700"><?= number_format($car['kms_driven']) ?> km</div>
                            <div class="text-[11px] text-slate-500"><?= $car['fuel_type'] ?> • <?= $car['transmission'] ?></div>
                        </td>
                        <td class="p-4">
                            <div class="font-black text-slate-800 text-sm"><?= format_rupee($car['price']) ?></div>
                            <div class="text-[11px] text-slate-400">EMI: ₹<?= number_format($car['emi_per_month'] ?? 12000) ?>/mo</div>
                        </td>
                        <td class="p-4">
                            <form action="" method="POST" class="inline">
                                <input type="hidden" name="action" value="update_status"/>
                                <input type="hidden" name="id" value="<?= $car['id'] ?>"/>
                                <select name="status" onchange="this.form.submit()" class="px-2.5 py-1 rounded-full text-[10px] font-bold border-0 cursor-pointer focus:outline-none <?= $car['status'] === 'Available' ? 'bg-emerald-100 text-emerald-800' : ($car['status'] === 'Sold' ? 'bg-slate-200 text-slate-700' : 'bg-amber-100 text-amber-800') ?>">
                                    <option value="Available" <?= ($car['status'] === 'Available') ? 'selected' : '' ?>>Available</option>
                                    <option value="Reserved" <?= ($car['status'] === 'Reserved') ? 'selected' : '' ?>>Reserved</option>
                                    <option value="Sold" <?= ($car['status'] === 'Sold') ? 'selected' : '' ?>>Sold</option>
                                </select>
                            </form>
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" onclick="openEditUsedModal(<?= htmlspecialchars(json_encode($car)) ?>)" class="p-1.5 rounded-lg text-slate-500 hover:text-secondary-container hover:bg-slate-100" title="Edit Car Details">
                                    <span class="material-symbols-outlined text-base">edit</span>
                                </button>
                                <form action="" method="POST" onsubmit="return confirm('Delete this vehicle from inventory?')" class="inline">
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

<!-- EDIT USED CAR MODAL -->
<div id="edit-used-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 shadow-2xl border border-slate-200 animate-fade-in">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-orange-100 text-orange-800 text-[10px] font-bold uppercase tracking-wider">Inventory Editor</span>
                <h3 class="text-lg font-bold text-slate-800 mt-1" id="edit-used-title">Edit Certified Used Car</h3>
            </div>
            <button onclick="document.getElementById('edit-used-modal').classList.add('hidden')" class="p-1 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="" method="POST" class="mt-5 space-y-4 text-xs">
            <input type="hidden" name="action" value="edit"/>
            <input type="hidden" name="id" id="edit-u-id" value=""/>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Listing Title *</label>
                <input type="text" name="title" id="edit-u-title" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Brand *</label>
                    <input type="text" name="brand" id="edit-u-brand" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Model *</label>
                    <input type="text" name="model" id="edit-u-model" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Variant</label>
                    <input type="text" name="variant" id="edit-u-variant" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Year *</label>
                    <input type="number" name="year" id="edit-u-year" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Price (₹) *</label>
                    <input type="number" name="price" id="edit-u-price" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Orig. Price (₹)</label>
                    <input type="number" name="original_price" id="edit-u-orig-price" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Est. EMI (₹/mo)</label>
                    <input type="number" name="emi_per_month" id="edit-u-emi" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">KMs Driven</label>
                    <input type="number" name="kms_driven" id="edit-u-kms" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Fuel Type</label>
                    <select name="fuel_type" id="edit-u-fuel" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        <option value="Petrol">Petrol</option>
                        <option value="Diesel">Diesel</option>
                        <option value="CNG">CNG</option>
                        <option value="Electric">Electric</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Transmission</label>
                    <select name="transmission" id="edit-u-transmission" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        <option value="Manual">Manual</option>
                        <option value="Automatic">Automatic</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Body Type</label>
                    <select name="body_type" id="edit-u-body" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        <option value="Hatchback">Hatchback</option>
                        <option value="Sedan">Sedan</option>
                        <option value="SUV">SUV</option>
                        <option value="MUV">MUV</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Status</label>
                    <select name="status" id="edit-u-status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        <option value="Available">Available</option>
                        <option value="Reserved">Reserved</option>
                        <option value="Sold">Sold</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Ownership</label>
                    <input type="text" name="ownership" id="edit-u-ownership" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Registration State</label>
                    <input type="text" name="registration_state" id="edit-u-reg" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Color</label>
                    <input type="text" name="color" id="edit-u-color" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Image URL *</label>
                <input type="url" name="image_url" id="edit-u-img" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-mono text-slate-800 focus:outline-none focus:border-primary"/>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Highlights (Comma separated)</label>
                <input type="text" name="highlights" id="edit-u-highlights" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none"/>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Detailed Description</label>
                <textarea name="description" id="edit-u-desc" rows="3" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none"></textarea>
            </div>

            <!-- SEO Optimization Suite -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="flex items-center gap-2 text-slate-800 font-bold">
                    <span class="material-symbols-outlined text-secondary-container text-base">travel_explore</span>
                    <span>Search Engine Optimization (SEO) &amp; OpenGraph</span>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">SEO URL Slug</label>
                    <input type="text" name="slug" id="edit-u-slug" placeholder="e.g. 2022-hyundai-venue-sx-turbo" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono text-slate-800 focus:outline-none focus:border-secondary-container"/>
                    <p class="text-[10px] text-slate-400 mt-0.5">Used in public vehicle URL: ezsafar.com/vehicle.php?slug=...</p>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Custom Meta Title</label>
                    <input type="text" name="meta_title" id="edit-u-meta-title" placeholder="Leave blank for auto-generated title" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-secondary-container"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Custom Meta Description</label>
                    <textarea name="meta_description" id="edit-u-meta-desc" rows="2" placeholder="Leave blank for auto-generated description (recommended 140-160 chars)" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-secondary-container"></textarea>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Meta Keywords</label>
                    <input type="text" name="meta_keywords" id="edit-u-meta-keywords" placeholder="used hyundai venue, buy used car jind, certified pre owned car" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-secondary-container"/>
                </div>
            </div>

            <div class="flex items-center gap-2 p-3 rounded-xl bg-slate-50 border border-slate-200">
                <input type="checkbox" name="is_featured" id="edit-u-featured" value="1" class="w-4 h-4 text-primary rounded"/>
                <label for="edit-u-featured" class="font-bold text-slate-700 cursor-pointer">Mark as Featured on Homepage</label>
            </div>

            <div class="pt-3 flex justify-end gap-3 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('edit-used-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-bold hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-secondary-container hover:bg-secondary text-white font-bold shadow flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">save</span>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Used Car Modal -->
<div id="add-used-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 shadow-2xl border border-slate-200 animate-fade-in">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <h3 class="text-lg font-bold text-slate-800">Add Certified Used Car</h3>
            <button onclick="document.getElementById('add-used-modal').classList.add('hidden')" class="p-1 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="" method="POST" class="mt-5 space-y-4 text-xs">
            <input type="hidden" name="action" value="add"/>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Listing Title *</label>
                <input type="text" name="title" required placeholder="e.g. 2022 Hyundai Venue SX Turbo" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Brand *</label>
                    <input type="text" name="brand" required placeholder="e.g. Hyundai" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Model *</label>
                    <input type="text" name="model" required placeholder="e.g. Venue" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Variant</label>
                    <input type="text" name="variant" placeholder="e.g. SX Turbo" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Year *</label>
                    <input type="number" name="year" value="2022" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Selling Price (₹) *</label>
                    <input type="number" name="price" value="750000" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Orig. Price (₹)</label>
                    <input type="number" name="original_price" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Est. EMI (₹/mo)</label>
                    <input type="number" name="emi_per_month" value="13500" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">KMs Driven</label>
                    <input type="number" name="kms_driven" value="28000" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Body Type</label>
                    <select name="body_type" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        <option value="Hatchback">Hatchback</option>
                        <option value="Sedan">Sedan</option>
                        <option value="SUV" selected>SUV</option>
                        <option value="MUV">MUV</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Fuel Type</label>
                    <select name="fuel_type" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        <option value="Petrol">Petrol</option>
                        <option value="Diesel">Diesel</option>
                        <option value="CNG">CNG</option>
                        <option value="Electric">Electric</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Transmission</label>
                    <select name="transmission" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        <option value="Manual">Manual</option>
                        <option value="Automatic">Automatic</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Image URL *</label>
                <input type="url" name="image_url" required placeholder="https://..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-mono text-slate-800 focus:outline-none focus:border-primary"/>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Highlights (Comma separated)</label>
                <input type="text" name="highlights" value="140-Point Inspected, 1-Year Comprehensive Warranty, Free RC Transfer, Non-Accidental" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none"/>
            </div>

            <!-- SEO Optimization Suite -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="flex items-center gap-2 text-slate-800 font-bold">
                    <span class="material-symbols-outlined text-secondary-container text-base">travel_explore</span>
                    <span>Search Engine Optimization (SEO) &amp; OpenGraph</span>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">SEO URL Slug (Optional)</label>
                    <input type="text" name="slug" placeholder="e.g. 2022-hyundai-venue-sx-turbo (auto-generated if empty)" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono text-slate-800 focus:outline-none focus:border-secondary-container"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Custom Meta Title</label>
                    <input type="text" name="meta_title" placeholder="Leave blank for auto-optimized title" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-secondary-container"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Custom Meta Description</label>
                    <textarea name="meta_description" rows="2" placeholder="Leave blank for auto-optimized description (140-160 chars)" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-secondary-container"></textarea>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Meta Keywords</label>
                    <input type="text" name="meta_keywords" placeholder="used hyundai venue, buy used car jind, certified pre owned car" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-secondary-container"/>
                </div>
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('add-used-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-bold hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-secondary-container hover:bg-secondary text-white font-bold shadow">
                    Save Car to Stock
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditUsedModal(car) {
    document.getElementById('edit-u-id').value = car.id;
    document.getElementById('edit-used-title').innerText = 'Edit ' + car.title;
    document.getElementById('edit-u-title').value = car.title;
    document.getElementById('edit-u-brand').value = car.brand;
    document.getElementById('edit-u-model').value = car.model;
    document.getElementById('edit-u-variant').value = car.variant || '';
    document.getElementById('edit-u-year').value = car.year;
    document.getElementById('edit-u-price').value = car.price;
    document.getElementById('edit-u-orig-price').value = car.original_price || '';
    document.getElementById('edit-u-emi').value = car.emi_per_month || '';
    document.getElementById('edit-u-kms').value = car.kms_driven;
    document.getElementById('edit-u-fuel').value = car.fuel_type;
    document.getElementById('edit-u-transmission').value = car.transmission;
    document.getElementById('edit-u-body').value = car.body_type;
    document.getElementById('edit-u-status').value = car.status;
    document.getElementById('edit-u-ownership').value = car.ownership || '1st Owner';
    document.getElementById('edit-u-reg').value = car.registration_state || '';
    document.getElementById('edit-u-color').value = car.color || '';
    document.getElementById('edit-u-img').value = car.image_url;
    document.getElementById('edit-u-highlights').value = car.highlights || '';
    document.getElementById('edit-u-desc').value = car.description || '';
    document.getElementById('edit-u-slug').value = car.slug || '';
    document.getElementById('edit-u-meta-title').value = car.meta_title || '';
    document.getElementById('edit-u-meta-desc').value = car.meta_description || '';
    document.getElementById('edit-u-meta-keywords').value = car.meta_keywords || '';
    document.getElementById('edit-u-featured').checked = (parseInt(car.is_featured) === 1);

    document.getElementById('edit-used-modal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
