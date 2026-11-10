<?php
$adminTitle = "Dashboard Overview";
require_once __DIR__ . '/includes/admin-header.php';

$db = get_db();

// Counts
$totalRentals = $db->query("SELECT COUNT(*) FROM rental_cars")->fetchColumn();
$activeRentals = $db->query("SELECT COUNT(*) FROM rental_cars WHERE is_available = 1")->fetchColumn();

$totalUsed = $db->query("SELECT COUNT(*) FROM used_cars")->fetchColumn();
$availableUsed = $db->query("SELECT COUNT(*) FROM used_cars WHERE status = 'Available'")->fetchColumn();

$totalBookings = $db->query("SELECT COUNT(*) FROM rental_bookings")->fetchColumn();
$pendingBookings = $db->query("SELECT COUNT(*) FROM rental_bookings WHERE booking_status = 'Pending'")->fetchColumn();

$totalSellLeads = $db->query("SELECT COUNT(*) FROM sell_requests")->fetchColumn();
$pendingSellLeads = $db->query("SELECT COUNT(*) FROM sell_requests WHERE status = 'Pending Review'")->fetchColumn();

$totalInquiries = $db->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();

// Recent Bookings
$recentBookings = $db->query("SELECT b.*, c.name as car_name FROM rental_bookings b LEFT JOIN rental_cars c ON b.car_id = c.id ORDER BY b.id DESC LIMIT 5")->fetchAll();

// Recent Sell Requests
$recentSellLeads = $db->query("SELECT * FROM sell_requests ORDER BY id DESC LIMIT 5")->fetchAll();
?>

<!-- KPI Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <!-- Rental Fleet -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Rental Fleet</div>
            <div class="text-3xl font-black text-slate-800 mt-1"><?= $totalRentals ?> Cars</div>
            <div class="text-xs text-emerald-600 font-semibold mt-1"><?= $activeRentals ?> Active on Fleet</div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl">directions_car</span>
        </div>
    </div>

    <!-- Used Cars -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Certified Used Cars</div>
            <div class="text-3xl font-black text-slate-800 mt-1"><?= $totalUsed ?> Total</div>
            <div class="text-xs text-orange-600 font-semibold mt-1"><?= $availableUsed ?> in Stock</div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-orange-50 text-secondary-container flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl">car_tag</span>
        </div>
    </div>

    <!-- Bookings -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Rental Bookings</div>
            <div class="text-3xl font-black text-slate-800 mt-1"><?= $totalBookings ?></div>
            <div class="text-xs text-blue-600 font-semibold mt-1"><?= $pendingBookings ?> Pending Confirmation</div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl">book_online</span>
        </div>
    </div>

    <!-- Sell Leads -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Sell Car Requests</div>
            <div class="text-3xl font-black text-slate-800 mt-1"><?= $totalSellLeads ?></div>
            <div class="text-xs text-amber-600 font-semibold mt-1"><?= $pendingSellLeads ?> Pending Valuation</div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl">payments</span>
        </div>
    </div>
</div>

<!-- Quick Action Shortcuts -->
<div class="mb-8 p-4 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-4">
    <div class="font-bold text-sm text-slate-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">add_circle</span>
        <span>Quick Management Actions:</span>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <a href="<?= BASE_URL ?>/admin/rental-cars.php#add" class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold shadow flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">add</span> Add Rental Car
        </a>
        <a href="<?= BASE_URL ?>/admin/used-cars.php#add" class="px-4 py-2 rounded-xl bg-secondary-container hover:bg-secondary text-white text-xs font-bold shadow flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">add</span> Add Certified Used Car
        </a>
        <a href="<?= BASE_URL ?>/admin/bookings.php" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">calendar_month</span> View All Bookings
        </a>
    </div>
</div>

<!-- Split Tables: Recent Bookings & Sell Requests -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Rental Bookings -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">calendar_today</span>
                <span>Recent Rental Bookings</span>
            </h3>
            <a href="<?= BASE_URL ?>/admin/bookings.php" class="text-xs text-primary font-bold hover:underline">View All &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-100">
                    <tr>
                        <th class="p-3.5">Booking ID</th>
                        <th class="p-3.5">Customer &amp; Car</th>
                        <th class="p-3.5">Dates</th>
                        <th class="p-3.5">Amount</th>
                        <th class="p-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($recentBookings)): ?>
                        <tr><td colspan="5" class="p-4 text-center text-slate-400">No bookings yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recentBookings as $b): ?>
                        <tr class="hover:bg-slate-50/80">
                            <td class="p-3.5 font-mono font-bold text-primary"><?= $b['booking_number'] ?></td>
                            <td class="p-3.5">
                                <div class="font-bold text-slate-800"><?= htmlspecialchars($b['customer_name']) ?></div>
                                <div class="text-[11px] text-slate-500"><?= htmlspecialchars($b['car_name'] ?? 'Car #' . $b['car_id']) ?></div>
                            </td>
                            <td class="p-3.5">
                                <div><?= date('d M', strtotime($b['pickup_date'])) ?> &rarr; <?= date('d M', strtotime($b['return_date'])) ?></div>
                                <div class="text-[10px] text-slate-400"><?= $b['total_days'] ?> day(s)</div>
                            </td>
                            <td class="p-3.5 font-bold"><?= format_rupee($b['total_amount']) ?></td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $b['booking_status'] === 'Confirmed' ? 'bg-emerald-100 text-emerald-800' : ($b['booking_status'] === 'Pending' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') ?>">
                                    <?= $b['booking_status'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Sell Requests -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary-container">sell</span>
                <span>Recent Sell Car Valuation Leads</span>
            </h3>
            <a href="<?= BASE_URL ?>/admin/sell-requests.php" class="text-xs text-secondary-container font-bold hover:underline">View All &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-100">
                    <tr>
                        <th class="p-3.5">Ref ID</th>
                        <th class="p-3.5">Car &amp; Reg</th>
                        <th class="p-3.5">Seller</th>
                        <th class="p-3.5">Est. Range</th>
                        <th class="p-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($recentSellLeads)): ?>
                        <tr><td colspan="5" class="p-4 text-center text-slate-400">No sell leads yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recentSellLeads as $s): ?>
                        <tr class="hover:bg-slate-50/80">
                            <td class="p-3.5 font-mono font-bold text-slate-700"><?= $s['request_number'] ?></td>
                            <td class="p-3.5">
                                <div class="font-bold text-slate-800"><?= $s['year'] ?> <?= htmlspecialchars($s['brand'] . ' ' . $s['model']) ?></div>
                                <div class="text-[11px] text-slate-500 font-mono"><?= htmlspecialchars($s['reg_number']) ?></div>
                            </td>
                            <td class="p-3.5">
                                <div><?= htmlspecialchars($s['seller_name']) ?></div>
                                <div class="text-[11px] text-slate-500"><?= htmlspecialchars($s['seller_phone']) ?></div>
                            </td>
                            <td class="p-3.5 font-semibold text-emerald-700">
                                <?= ($s['estimated_range_min'] > 0) ? format_rupee($s['estimated_range_min']) : 'Custom' ?>
                            </td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $s['status'] === 'Inspection Scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' ?>">
                                    <?= $s['status'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>

