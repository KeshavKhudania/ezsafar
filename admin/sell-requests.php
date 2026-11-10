<?php
require_once __DIR__ . '/auth-check.php';
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'update_lead_status') {
        $leadId = (int)($_POST['lead_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? 'Inspection Scheduled');
        $notes = sanitize($_POST['admin_notes'] ?? '');
        
        $stmt = $db->prepare("UPDATE sell_requests SET status = ?, admin_notes = ? WHERE id = ?");
        $stmt->execute([$status, $notes, $leadId]);
        set_flash('info', "Lead status updated to $status.");
    } elseif ($action === 'edit_lead') {
        $leadId = (int)($_POST['id'] ?? 0);
        $regNumber = strtoupper(sanitize($_POST['reg_number'] ?? ''));
        $brand = sanitize($_POST['brand'] ?? '');
        $model = sanitize($_POST['model'] ?? '');
        $year = (int)($_POST['year'] ?? 2021);
        $fuel = sanitize($_POST['fuel_type'] ?? 'Petrol');
        $transmission = sanitize($_POST['transmission'] ?? 'Manual');
        $kms = (int)($_POST['kms_driven'] ?? 30000);
        $condition = sanitize($_POST['car_condition'] ?? 'Good');
        $expected = (float)($_POST['expected_price'] ?? 0);
        $estMin = (float)($_POST['estimated_range_min'] ?? 0);
        $estMax = (float)($_POST['estimated_range_max'] ?? 0);
        $sellerName = sanitize($_POST['seller_name'] ?? '');
        $sellerPhone = sanitize($_POST['seller_phone'] ?? '');
        $sellerCity = sanitize($_POST['seller_city'] ?? 'Jind');
        $inspectionDate = !empty($_POST['inspection_pref_date']) ? sanitize($_POST['inspection_pref_date']) : null;
        $status = sanitize($_POST['status'] ?? 'Pending Review');
        $adminNotes = sanitize($_POST['admin_notes'] ?? '');

        if ($leadId > 0 && !empty($sellerName) && !empty($sellerPhone)) {
            $stmt = $db->prepare("UPDATE sell_requests SET 
                reg_number=?, brand=?, model=?, year=?, fuel_type=?, transmission=?, 
                kms_driven=?, car_condition=?, expected_price=?, estimated_range_min=?, 
                estimated_range_max=?, seller_name=?, seller_phone=?, seller_city=?, 
                inspection_pref_date=?, status=?, admin_notes=?
                WHERE id=?");
            $stmt->execute([
                $regNumber, $brand, $model, $year, $fuel, $transmission,
                $kms, $condition, $expected, $estMin, $estMax,
                $sellerName, $sellerPhone, $sellerCity, $inspectionDate,
                $status, $adminNotes, $leadId
            ]);
            set_flash('success', "Lead #$leadId updated successfully.");
        } else {
            set_flash('error', "Seller name and phone number are required.");
        }
    }

    header('Location: ' . BASE_URL . '/admin/sell-requests.php');
    exit;
}

$adminTitle = "Sell Car Leads & Valuations";
require_once __DIR__ . '/includes/admin-header.php';

$leads = $db->query("SELECT * FROM sell_requests ORDER BY id DESC")->fetchAll();
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-xl font-bold text-slate-800">Sell Car Valuation Leads (<?= count($leads) ?> Total)</h3>
        <p class="text-xs text-slate-500">Review, edit vehicle data, schedule doorstep inspections, and manage cash offers.</p>
    </div>
</div>

<!-- Leads Table -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-200">
                <tr>
                    <th class="p-4">Ref Number</th>
                    <th class="p-4">Vehicle Details</th>
                    <th class="p-4">Seller &amp; City</th>
                    <th class="p-4">Condition &amp; KMs</th>
                    <th class="p-4">Expected vs AI Est</th>
                    <th class="p-4">Inspection Date</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($leads)): ?>
                    <tr><td colspan="8" class="p-8 text-center text-slate-400">No sell leads submitted yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($leads as $lead): ?>
                    <tr class="hover:bg-slate-50/70">
                        <td class="p-4">
                            <div class="font-mono font-bold text-slate-800 text-sm"><?= $lead['request_number'] ?></div>
                            <div class="text-[10px] text-slate-400"><?= date('d M Y', strtotime($lead['created_at'])) ?></div>
                        </td>
                        <td class="p-4">
                            <div class="font-black text-slate-800 text-sm"><?= $lead['year'] ?> <?= htmlspecialchars($lead['brand'] . ' ' . $lead['model']) ?></div>
                            <div class="font-mono text-primary font-bold text-[11px]"><?= htmlspecialchars($lead['reg_number']) ?></div>
                            <div class="text-slate-500 text-[10px]"><?= $lead['fuel_type'] ?> • <?= $lead['transmission'] ?></div>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800"><?= htmlspecialchars($lead['seller_name']) ?></div>
                            <div>
                                <a href="tel:<?= htmlspecialchars($lead['seller_phone']) ?>" class="text-primary font-semibold hover:underline"><?= htmlspecialchars($lead['seller_phone']) ?></a>
                            </div>
                            <div class="text-[10px] text-slate-400"><?= htmlspecialchars($lead['seller_city']) ?></div>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-700"><?= number_format($lead['kms_driven']) ?> km</div>
                            <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold <?= $lead['car_condition'] === 'Excellent' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' ?>">
                                <?= $lead['car_condition'] ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <?php if ($lead['expected_price'] > 0): ?>
                                <div><strong>Expected:</strong> <?= format_rupee($lead['expected_price']) ?></div>
                            <?php endif; ?>
                            <div class="text-emerald-700 font-bold mt-0.5">
                                AI: <?= ($lead['estimated_range_min'] > 0) ? (format_rupee($lead['estimated_range_min']) . ' - ' . format_rupee($lead['estimated_range_max'])) : 'N/A' ?>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="font-semibold text-slate-700">
                                <?= $lead['inspection_pref_date'] ? date('d M Y', strtotime($lead['inspection_pref_date'])) : 'Not scheduled' ?>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $lead['status'] === 'Deal Closed' ? 'bg-emerald-100 text-emerald-800' : ($lead['status'] === 'Inspection Scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') ?>">
                                <?= $lead['status'] ?>
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <button type="button" onclick="openEditLeadModal(<?= htmlspecialchars(json_encode($lead)) ?>)" class="p-1.5 rounded-lg text-slate-500 hover:text-secondary-container hover:bg-slate-100" title="Edit Full Lead Data">
                                <span class="material-symbols-outlined text-base">edit_note</span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- EDIT LEAD MODAL -->
<div id="edit-lead-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 shadow-2xl border border-slate-200 animate-fade-in">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-orange-100 text-orange-800 text-[10px] font-bold uppercase tracking-wider">Lead Editor</span>
                <h3 class="text-lg font-bold text-slate-800 mt-1" id="edit-l-title">Edit Valuation Request</h3>
            </div>
            <button onclick="document.getElementById('edit-lead-modal').classList.add('hidden')" class="p-1 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="" method="POST" class="mt-5 space-y-4 text-xs">
            <input type="hidden" name="action" value="edit_lead"/>
            <input type="hidden" name="id" id="edit-l-id" value=""/>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Vehicle Reg Number *</label>
                    <input type="text" name="reg_number" id="edit-l-reg" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono font-bold text-slate-800 uppercase focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Brand *</label>
                    <input type="text" name="brand" id="edit-l-brand" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 focus:outline-none"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Model *</label>
                    <input type="text" name="model" id="edit-l-model" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 focus:outline-none"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Year</label>
                    <input type="number" name="year" id="edit-l-year" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold text-slate-800"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Fuel Type</label>
                    <select name="fuel_type" id="edit-l-fuel" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold text-slate-800">
                        <option value="Petrol">Petrol</option>
                        <option value="Diesel">Diesel</option>
                        <option value="CNG">CNG</option>
                        <option value="Electric">Electric</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Transmission</label>
                    <select name="transmission" id="edit-l-transmission" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold text-slate-800">
                        <option value="Manual">Manual</option>
                        <option value="Automatic">Automatic</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">KMs Driven</label>
                    <input type="number" name="kms_driven" id="edit-l-kms" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold text-slate-800"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Car Condition</label>
                    <select name="car_condition" id="edit-l-condition" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold text-slate-800">
                        <option value="Excellent">Excellent</option>
                        <option value="Good">Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Needs Work">Needs Work</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Expected Price (₹)</label>
                    <input type="number" name="expected_price" id="edit-l-expected" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold text-slate-800"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Inspection Date</label>
                    <input type="date" name="inspection_pref_date" id="edit-l-date" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold text-slate-800"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">AI Est. Min (₹)</label>
                    <input type="number" name="estimated_range_min" id="edit-l-min" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold text-slate-800"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">AI Est. Max (₹)</label>
                    <input type="number" name="estimated_range_max" id="edit-l-max" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold text-slate-800"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Seller Name *</label>
                    <input type="text" name="seller_name" id="edit-l-name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-800 focus:outline-none"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Seller Phone *</label>
                    <input type="tel" name="seller_phone" id="edit-l-phone" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-800 focus:outline-none"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Seller City / Hub</label>
                    <input type="text" name="seller_city" id="edit-l-city" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 focus:outline-none"/>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Valuation Status</label>
                <select name="status" id="edit-l-status" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-800">
                    <option value="Pending Review">Pending Review</option>
                    <option value="Inspection Scheduled">Inspection Scheduled</option>
                    <option value="Offer Generated">Offer Generated</option>
                    <option value="Deal Closed">Deal Closed</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Admin Inspection &amp; Deal Notes</label>
                <textarea name="admin_notes" id="edit-l-notes" rows="2" placeholder="e.g. Inspector found slight scratch on rear bumper. Final counter offer ₹6,15,000 accepted." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-3 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('edit-lead-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-bold hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-secondary-container hover:bg-secondary text-white font-bold shadow flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">save</span>
                    <span>Save Lead Details</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditLeadModal(lead) {
    document.getElementById('edit-l-id').value = lead.id;
    document.getElementById('edit-l-title').innerText = 'Edit ' + lead.request_number;
    document.getElementById('edit-l-reg').value = lead.reg_number;
    document.getElementById('edit-l-brand').value = lead.brand;
    document.getElementById('edit-l-model').value = lead.model;
    document.getElementById('edit-l-year').value = lead.year;
    document.getElementById('edit-l-fuel').value = lead.fuel_type;
    document.getElementById('edit-l-transmission').value = lead.transmission;
    document.getElementById('edit-l-kms').value = lead.kms_driven;
    document.getElementById('edit-l-condition').value = lead.car_condition;
    document.getElementById('edit-l-expected').value = lead.expected_price || '';
    document.getElementById('edit-l-min').value = lead.estimated_range_min || '';
    document.getElementById('edit-l-max').value = lead.estimated_range_max || '';
    document.getElementById('edit-l-date').value = lead.inspection_pref_date || '';
    document.getElementById('edit-l-name').value = lead.seller_name;
    document.getElementById('edit-l-phone').value = lead.seller_phone;
    document.getElementById('edit-l-city').value = lead.seller_city || 'Jind';
    document.getElementById('edit-l-status').value = lead.status;
    document.getElementById('edit-l-notes').value = lead.admin_notes || '';

    document.getElementById('edit-lead-modal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
