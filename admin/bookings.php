<?php
require_once __DIR__ . '/auth-check.php';
$db = get_db();

// Handle status updates & full edits
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'update_booking_status') {
        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? 'Confirmed');
        $stmt = $db->prepare("UPDATE rental_bookings SET booking_status = ? WHERE id = ?");
        $stmt->execute([$status, $bookingId]);
        set_flash('info', "Booking status updated to $status.");
    } elseif ($action === 'edit_booking') {
        $bookingId = (int)($_POST['id'] ?? 0);
        $name = sanitize($_POST['customer_name'] ?? '');
        $phone = sanitize($_POST['customer_phone'] ?? '');
        $email = sanitize($_POST['customer_email'] ?? '');
        $city = sanitize($_POST['customer_city'] ?? 'Jind');
        $rentalType = sanitize($_POST['rental_type'] ?? 'Self-Drive');
        $pickupDate = sanitize($_POST['pickup_date'] ?? '');
        $pickupTime = sanitize($_POST['pickup_time'] ?? '');
        $returnDate = sanitize($_POST['return_date'] ?? '');
        $returnTime = sanitize($_POST['return_time'] ?? '');
        $pickupLoc = sanitize($_POST['pickup_location'] ?? '');
        $dropLoc = sanitize($_POST['drop_location'] ?? '');
        $days = (int)($_POST['total_days'] ?? 1);
        $amount = (float)($_POST['total_amount'] ?? 0);
        $status = sanitize($_POST['booking_status'] ?? 'Pending');
        $payStatus = sanitize($_POST['payment_status'] ?? 'Pay at Pickup');
        $special = sanitize($_POST['special_requests'] ?? '');

        if ($bookingId > 0 && !empty($name) && !empty($phone)) {
            $stmt = $db->prepare("UPDATE rental_bookings SET 
                customer_name=?, customer_phone=?, customer_email=?, customer_city=?, 
                rental_type=?, pickup_date=?, pickup_time=?, return_date=?, return_time=?, 
                pickup_location=?, drop_location=?, total_days=?, total_amount=?, 
                booking_status=?, payment_status=?, special_requests=?
                WHERE id=?");
            $stmt->execute([
                $name, $phone, $email, $city, $rentalType, $pickupDate, $pickupTime,
                $returnDate, $returnTime, $pickupLoc, $dropLoc, $days, $amount,
                $status, $payStatus, $special, $bookingId
            ]);
            set_flash('success', "Booking #$bookingId updated successfully.");
        } else {
            set_flash('error', "Customer name and phone number are required.");
        }
    }

    header('Location: ' . BASE_URL . '/admin/bookings.php');
    exit;
}

$adminTitle = "Rental Bookings";
require_once __DIR__ . '/includes/admin-header.php';

$statusFilter = sanitize($_GET['status'] ?? '');
$sql = "SELECT b.*, c.name as car_name, c.image_url as car_image FROM rental_bookings b LEFT JOIN rental_cars c ON b.car_id = c.id";
$params = [];
if ($statusFilter) {
    $sql .= " WHERE b.booking_status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY b.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-xl font-bold text-slate-800">Rental Bookings (<?= count($bookings) ?>)</h3>
        <p class="text-xs text-slate-500">Track, edit customer details, and manage reservation statuses.</p>
    </div>
    
    <!-- Filter Tabs -->
    <div class="flex flex-wrap items-center gap-1.5 bg-white p-1.5 rounded-xl border border-slate-200">
        <a href="<?= BASE_URL ?>/admin/bookings.php" class="px-3 py-1.5 rounded-lg text-xs font-bold <?= empty($statusFilter) ? 'bg-primary text-white shadow' : 'text-slate-600 hover:bg-slate-100' ?>">All</a>
        <a href="<?= BASE_URL ?>/admin/bookings.php?status=Pending" class="px-3 py-1.5 rounded-lg text-xs font-bold <?= ($statusFilter === 'Pending') ? 'bg-primary text-white shadow' : 'text-slate-600 hover:bg-slate-100' ?>">Pending</a>
        <a href="<?= BASE_URL ?>/admin/bookings.php?status=Confirmed" class="px-3 py-1.5 rounded-lg text-xs font-bold <?= ($statusFilter === 'Confirmed') ? 'bg-primary text-white shadow' : 'text-slate-600 hover:bg-slate-100' ?>">Confirmed</a>
        <a href="<?= BASE_URL ?>/admin/bookings.php?status=Active" class="px-3 py-1.5 rounded-lg text-xs font-bold <?= ($statusFilter === 'Active') ? 'bg-primary text-white shadow' : 'text-slate-600 hover:bg-slate-100' ?>">Active (On Road)</a>
        <a href="<?= BASE_URL ?>/admin/bookings.php?status=Completed" class="px-3 py-1.5 rounded-lg text-xs font-bold <?= ($statusFilter === 'Completed') ? 'bg-primary text-white shadow' : 'text-slate-600 hover:bg-slate-100' ?>">Completed</a>
    </div>
</div>

<!-- Bookings Table -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-200">
                <tr>
                    <th class="p-4">Booking Ref</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Car &amp; Mode</th>
                    <th class="p-4">Dates &amp; Duration</th>
                    <th class="p-4">Locations</th>
                    <th class="p-4">Total Amount</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($bookings)): ?>
                    <tr><td colspan="8" class="p-8 text-center text-slate-400">No bookings found for this filter.</td></tr>
                <?php endif; ?>
                <?php foreach ($bookings as $b): ?>
                    <tr class="hover:bg-slate-50/70">
                        <td class="p-4">
                            <div class="font-mono font-black text-primary text-sm"><?= $b['booking_number'] ?></div>
                            <div class="text-[10px] text-slate-400 mt-0.5"><?= date('d M Y, h:i A', strtotime($b['created_at'])) ?></div>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($b['customer_name']) ?></div>
                            <div class="text-[11px] text-slate-500">
                                <a href="tel:<?= htmlspecialchars($b['customer_phone']) ?>" class="hover:text-primary font-semibold"><?= htmlspecialchars($b['customer_phone']) ?></a>
                            </div>
                            <?php if (!empty($b['customer_email'])): ?>
                                <div class="text-[10px] text-slate-400"><?= htmlspecialchars($b['customer_email']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800"><?= htmlspecialchars($b['car_name'] ?? 'Car #' . $b['car_id']) ?></div>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold <?= $b['rental_type'] === 'Self-Drive' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' ?>">
                                <?= $b['rental_type'] ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="font-semibold text-slate-700"><?= date('d M Y', strtotime($b['pickup_date'])) ?> (<?= date('h:i A', strtotime($b['pickup_time'])) ?>)</div>
                            <div class="text-slate-500">to <?= date('d M Y', strtotime($b['return_date'])) ?></div>
                            <div class="text-[10px] text-slate-400 font-bold"><?= $b['total_days'] ?> Day(s) Total</div>
                        </td>
                        <td class="p-4 text-slate-600">
                            <div><strong>Pickup:</strong> <?= htmlspecialchars($b['pickup_location']) ?></div>
                            <div><strong>Drop:</strong> <?= htmlspecialchars($b['drop_location']) ?></div>
                        </td>
                        <td class="p-4">
                            <div class="font-black text-slate-800 text-sm"><?= format_rupee($b['total_amount']) ?></div>
                            <div class="text-[10px] text-slate-400"><?= $b['payment_status'] ?></div>
                        </td>
                        <td class="p-4">
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="update_booking_status"/>
                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>"/>
                                <select name="status" onchange="this.form.submit()" class="px-2.5 py-1.5 rounded-xl text-xs font-bold border-0 cursor-pointer focus:outline-none <?= $b['booking_status'] === 'Confirmed' ? 'bg-emerald-100 text-emerald-800' : ($b['booking_status'] === 'Pending' ? 'bg-amber-100 text-amber-800' : ($b['booking_status'] === 'Active' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700')) ?>">
                                    <option value="Pending" <?= ($b['booking_status'] === 'Pending') ? 'selected' : '' ?>>Pending</option>
                                    <option value="Confirmed" <?= ($b['booking_status'] === 'Confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="Active" <?= ($b['booking_status'] === 'Active') ? 'selected' : '' ?>>Active (Dispatched)</option>
                                    <option value="Completed" <?= ($b['booking_status'] === 'Completed') ? 'selected' : '' ?>>Completed</option>
                                    <option value="Cancelled" <?= ($b['booking_status'] === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </form>
                        </td>
                        <td class="p-4 text-right">
                            <button type="button" onclick="openEditBookingModal(<?= htmlspecialchars(json_encode($b)) ?>)" class="p-1.5 rounded-lg text-slate-500 hover:text-primary hover:bg-slate-100" title="Edit Full Booking Details">
                                <span class="material-symbols-outlined text-base">edit_note</span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- EDIT BOOKING MODAL -->
<div id="edit-booking-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 shadow-2xl border border-slate-200 animate-fade-in">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-bold uppercase tracking-wider">Booking Editor</span>
                <h3 class="text-lg font-bold text-slate-800 mt-1" id="edit-b-title">Edit Reservation</h3>
            </div>
            <button onclick="document.getElementById('edit-booking-modal').classList.add('hidden')" class="p-1 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="" method="POST" class="mt-5 space-y-4 text-xs">
            <input type="hidden" name="action" value="edit_booking"/>
            <input type="hidden" name="id" id="edit-b-id" value=""/>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Customer Name *</label>
                    <input type="text" name="customer_name" id="edit-b-name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Phone Number *</label>
                    <input type="tel" name="customer_phone" id="edit-b-phone" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Email</label>
                    <input type="email" name="customer_email" id="edit-b-email" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Customer City</label>
                    <input type="text" name="customer_city" id="edit-b-city" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Pickup Date &amp; Time</label>
                    <div class="flex gap-2">
                        <input type="date" name="pickup_date" id="edit-b-pdate" class="w-2/3 px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                        <input type="time" name="pickup_time" id="edit-b-ptime" class="w-1/3 px-2 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Return Date &amp; Time</label>
                    <div class="flex gap-2">
                        <input type="date" name="return_date" id="edit-b-rdate" class="w-2/3 px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                        <input type="time" name="return_time" id="edit-b-rtime" class="w-1/3 px-2 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Pickup Location</label>
                    <input type="text" name="pickup_location" id="edit-b-ploc" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Drop Location</label>
                    <input type="text" name="drop_location" id="edit-b-dloc" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Rental Type</label>
                    <select name="rental_type" id="edit-b-type" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        <option value="Self-Drive">Self-Drive</option>
                        <option value="With Chauffeur">With Chauffeur</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Total Days</label>
                    <input type="number" name="total_days" id="edit-b-days" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Total Amount (₹)</label>
                    <input type="number" step="0.5" name="total_amount" id="edit-b-amount" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Payment Status</label>
                    <select name="payment_status" id="edit-b-pay" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                        <option value="Pay at Pickup">Pay at Pickup</option>
                        <option value="Deposit Paid">Deposit Paid</option>
                        <option value="Fully Paid">Fully Paid</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Booking Status</label>
                <select name="booking_status" id="edit-b-status" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-bold">
                    <option value="Pending">Pending</option>
                    <option value="Confirmed">Confirmed</option>
                    <option value="Active">Active (Dispatched)</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Special Requests / Notes</label>
                <textarea name="special_requests" id="edit-b-special" rows="2" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-3 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('edit-booking-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-bold hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white font-bold shadow flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">save</span>
                    <span>Save Booking</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditBookingModal(b) {
    document.getElementById('edit-b-id').value = b.id;
    document.getElementById('edit-b-title').innerText = 'Edit ' + b.booking_number;
    document.getElementById('edit-b-name').value = b.customer_name;
    document.getElementById('edit-b-phone').value = b.customer_phone;
    document.getElementById('edit-b-email').value = b.customer_email || '';
    document.getElementById('edit-b-city').value = b.customer_city || 'Jind';
    document.getElementById('edit-b-type').value = b.rental_type;
    document.getElementById('edit-b-pdate').value = b.pickup_date;
    document.getElementById('edit-b-ptime').value = b.pickup_time ? b.pickup_time.substring(0, 5) : '09:00';
    document.getElementById('edit-b-rdate').value = b.return_date;
    document.getElementById('edit-b-rtime').value = b.return_time ? b.return_time.substring(0, 5) : '20:00';
    document.getElementById('edit-b-ploc').value = b.pickup_location;
    document.getElementById('edit-b-dloc').value = b.drop_location;
    document.getElementById('edit-b-days').value = b.total_days;
    document.getElementById('edit-b-amount').value = b.total_amount;
    document.getElementById('edit-b-pay').value = b.payment_status;
    document.getElementById('edit-b-status').value = b.booking_status;
    document.getElementById('edit-b-special').value = b.special_requests || '';

    document.getElementById('edit-booking-modal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
