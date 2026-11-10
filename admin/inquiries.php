<?php
require_once __DIR__ . '/auth-check.php';
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'update_inquiry') {
        $inquiryId = (int)($_POST['inquiry_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? 'Contacted');
        
        $stmt = $db->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
        $stmt->execute([$status, $inquiryId]);
        set_flash('info', "Inquiry marked as $status.");
    } elseif ($action === 'edit_inquiry') {
        $inquiryId = (int)($_POST['id'] ?? 0);
        $type = sanitize($_POST['inquiry_type'] ?? 'General');
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $prefDate = !empty($_POST['preferred_date']) ? sanitize($_POST['preferred_date']) : null;
        $message = sanitize($_POST['message'] ?? '');
        $status = sanitize($_POST['status'] ?? 'New');

        if ($inquiryId > 0 && !empty($name) && !empty($phone)) {
            $stmt = $db->prepare("UPDATE inquiries SET inquiry_type=?, name=?, phone=?, email=?, preferred_date=?, message=?, status=? WHERE id=?");
            $stmt->execute([$type, $name, $phone, $email, $prefDate, $message, $status, $inquiryId]);
            set_flash('success', "Inquiry #$inquiryId updated successfully.");
        } else {
            set_flash('error', "Name and phone number are required.");
        }
    }

    header('Location: ' . BASE_URL . '/admin/inquiries.php');
    exit;
}

$adminTitle = "Inquiries & Test Drives";
require_once __DIR__ . '/includes/admin-header.php';

$inquiries = $db->query("SELECT i.*, u.title as car_title, u.image_url as car_image 
                         FROM inquiries i 
                         LEFT JOIN used_cars u ON i.car_id = u.id 
                         ORDER BY i.id DESC")->fetchAll();
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-xl font-bold text-slate-800">Inquiries &amp; Test Drive Requests (<?= count($inquiries) ?>)</h3>
        <p class="text-xs text-slate-500">Respond to customer test drives and update inquiry details.</p>
    </div>
</div>

<!-- Table -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-200">
                <tr>
                    <th class="p-4">Type</th>
                    <th class="p-4">Customer Details</th>
                    <th class="p-4">Requested Vehicle</th>
                    <th class="p-4">Preferred Date</th>
                    <th class="p-4">Message / Notes</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($inquiries)): ?>
                    <tr><td colspan="7" class="p-8 text-center text-slate-400">No customer inquiries recorded yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($inquiries as $inq): ?>
                    <tr class="hover:bg-slate-50/70">
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $inq['inquiry_type'] === 'Test Drive' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' ?>">
                                <?= $inq['inquiry_type'] ?>
                            </span>
                            <div class="text-[10px] text-slate-400 mt-1"><?= date('d M Y', strtotime($inq['created_at'])) ?></div>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($inq['name']) ?></div>
                            <div>
                                <a href="tel:<?= htmlspecialchars($inq['phone']) ?>" class="text-primary font-semibold hover:underline"><?= htmlspecialchars($inq['phone']) ?></a>
                            </div>
                            <?php if (!empty($inq['email'])): ?>
                                <div class="text-[10px] text-slate-400"><?= htmlspecialchars($inq['email']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="p-4">
                            <?php if (!empty($inq['car_title'])): ?>
                                <div class="font-bold text-slate-800"><?= htmlspecialchars($inq['car_title']) ?></div>
                            <?php else: ?>
                                <span class="text-slate-400 italic">General Query</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4">
                            <div class="font-semibold text-slate-700">
                                <?= $inq['preferred_date'] ? date('d M Y', strtotime($inq['preferred_date'])) : 'Anytime' ?>
                            </div>
                        </td>
                        <td class="p-4 max-w-xs">
                            <p class="text-slate-600 truncate" title="<?= htmlspecialchars($inq['message'] ?? '') ?>">
                                <?= htmlspecialchars($inq['message'] ?? 'No message provided.') ?>
                            </p>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $inq['status'] === 'New' ? 'bg-rose-100 text-rose-800' : ($inq['status'] === 'Contacted' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800') ?>">
                                <?= $inq['status'] ?>
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <button type="button" onclick="openEditInquiryModal(<?= htmlspecialchars(json_encode($inq)) ?>)" class="p-1.5 rounded-lg text-slate-500 hover:text-primary hover:bg-slate-100" title="Edit Inquiry / Response">
                                <span class="material-symbols-outlined text-base">edit_note</span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- EDIT INQUIRY MODAL -->
<div id="edit-inquiry-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 shadow-2xl border border-slate-200 animate-fade-in">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-bold uppercase tracking-wider">Inquiry Editor</span>
                <h3 class="text-lg font-bold text-slate-800 mt-1" id="edit-i-title">Edit Inquiry</h3>
            </div>
            <button onclick="document.getElementById('edit-inquiry-modal').classList.add('hidden')" class="p-1 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="" method="POST" class="mt-5 space-y-4 text-xs">
            <input type="hidden" name="action" value="edit_inquiry"/>
            <input type="hidden" name="id" id="edit-i-id" value=""/>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Inquiry Type</label>
                    <select name="inquiry_type" id="edit-i-type" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold">
                        <option value="General">General</option>
                        <option value="Test Drive">Test Drive</option>
                        <option value="Used Car Purchase">Used Car Purchase</option>
                        <option value="Rental Inquiry">Rental Inquiry</option>
                        <option value="Corporate">Corporate</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Preferred Date</label>
                    <input type="date" name="preferred_date" id="edit-i-date" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Customer Name *</label>
                    <input type="text" name="name" id="edit-i-name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-800 focus:outline-none"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Customer Phone *</label>
                    <input type="tel" name="phone" id="edit-i-phone" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-800 focus:outline-none"/>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Customer Email</label>
                <input type="email" name="email" id="edit-i-email" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Status</label>
                <select name="status" id="edit-i-status" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-bold">
                    <option value="New">New</option>
                    <option value="Contacted">Contacted</option>
                    <option value="Resolved">Resolved</option>
                    <option value="Closed">Closed</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Message / Notes</label>
                <textarea name="message" id="edit-i-msg" rows="3" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-3 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('edit-inquiry-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-bold hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white font-bold shadow flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">save</span>
                    <span>Save Inquiry</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditInquiryModal(inq) {
    document.getElementById('edit-i-id').value = inq.id;
    document.getElementById('edit-i-title').innerText = 'Edit ' + inq.inquiry_type + ' #' + inq.id;
    document.getElementById('edit-i-type').value = inq.inquiry_type;
    document.getElementById('edit-i-name').value = inq.name;
    document.getElementById('edit-i-phone').value = inq.phone;
    document.getElementById('edit-i-email').value = inq.email || '';
    document.getElementById('edit-i-date').value = inq.preferred_date || '';
    document.getElementById('edit-i-status').value = inq.status;
    document.getElementById('edit-i-msg').value = inq.message || '';

    document.getElementById('edit-inquiry-modal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
