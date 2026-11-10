<?php
require_once __DIR__ . '/auth-check.php';
$db = get_db();

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $submitted = $_POST['settings'] ?? [];

    $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");

    foreach ($submitted as $key => $val) {
        $cleanKey = sanitize($key);
        // Do not escape HTML for custom_head_scripts so tags like <script> or <meta> remain intact
        $cleanVal = ($cleanKey === 'custom_head_scripts') ? trim($val) : sanitize($val);
        $stmt->execute([$cleanVal, $cleanKey]);
    }

    set_flash('success', "Website configurations and SEO metadata updated successfully.");
    header('Location: ' . BASE_URL . '/admin/settings.php');
    exit;
}

$adminTitle = "Website & SEO Settings";
require_once __DIR__ . '/includes/admin-header.php';

// Fetch all settings ordered by group
$stmt = $db->query("SELECT * FROM settings ORDER BY setting_group ASC, setting_key ASC");
$allSettings = $stmt->fetchAll();

// Group settings
$grouped = [];
foreach ($allSettings as $s) {
    $grouped[$s['setting_group']][] = $s;
}

$activeTab = sanitize($_GET['tab'] ?? 'general');
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-xl font-bold text-slate-800">Website &amp; SEO Configuration</h3>
        <p class="text-xs text-slate-500">Manage global brand information, SEO meta tags, social profiles, and operational parameters.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?= BASE_URL ?>/index.php" target="_blank" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold shadow-sm hover:bg-slate-50 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">visibility</span>
            <span>Preview Live Site</span>
        </a>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="flex flex-wrap items-center gap-2 mb-6 border-b border-slate-200 pb-3">
    <button type="button" onclick="switchSettingsTab('general')" id="tab-btn-general" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all bg-primary text-white shadow-md">
        <span class="material-symbols-outlined text-base">storefront</span>
        <span>General &amp; Business</span>
    </button>
    <button type="button" onclick="switchSettingsTab('seo')" id="tab-btn-seo" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-100">
        <span class="material-symbols-outlined text-base">search</span>
        <span>Search Engine Optimization (SEO)</span>
    </button>
    <button type="button" onclick="switchSettingsTab('pricing')" id="tab-btn-pricing" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-100">
        <span class="material-symbols-outlined text-base">price_change</span>
        <span>Rental &amp; Fleet Policies</span>
    </button>
    <button type="button" onclick="switchSettingsTab('social')" id="tab-btn-social" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-100">
        <span class="material-symbols-outlined text-base">share</span>
        <span>Social Profiles</span>
    </button>
</div>

<form action="" method="POST" id="settings-form" class="space-y-6">
    <input type="hidden" name="action" value="save_settings"/>

    <!-- TAB 1: GENERAL & BUSINESS INFO -->
    <div id="tab-general" class="settings-tab-content bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <div>
            <h4 class="text-base font-bold text-slate-800">General Information</h4>
            <p class="text-xs text-slate-500">Core identity and contact details displayed to customers across the site.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 text-xs">
            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Website Brand Name</label>
                <input type="text" name="settings[site_name]" value="<?= htmlspecialchars(get_setting('site_name', 'EzSafar')) ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-sm focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">Displayed on the logo and page titles.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Tagline</label>
                <input type="text" name="settings[site_tagline]" value="<?= htmlspecialchars(get_setting('site_tagline', 'Automotive Mobility Platform')) ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-sm focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">Short subtitle for brand positioning.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Currency Symbol</label>
                <input type="text" name="settings[currency_symbol]" value="<?= htmlspecialchars(get_setting('currency_symbol', '₹')) ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-800 text-sm focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">Default: ₹ (Indian Rupee)</p>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Primary Operating City</label>
                <input type="text" name="settings[primary_city]" value="<?= htmlspecialchars(get_setting('primary_city', 'Jind')) ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-sm focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">Base hub city in Haryana.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Primary Operating State</label>
                <input type="text" name="settings[primary_state]" value="<?= htmlspecialchars(get_setting('primary_state', 'Haryana')) ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-sm focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">State jurisdiction.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Customer Support Phone</label>
                <input type="text" name="settings[contact_phone]" value="<?= htmlspecialchars(get_setting('contact_phone', '+91 9729226643')) ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-sm focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">Click-to-call link in header and footer.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">WhatsApp Support Number</label>
                <input type="text" name="settings[contact_whatsapp]" value="<?= htmlspecialchars(get_setting('contact_whatsapp', '919729226643')) ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-sm focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">Format: Country code + number without plus (e.g. 919729226643).</p>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Support Email</label>
                <input type="email" name="settings[contact_email]" value="<?= htmlspecialchars(get_setting('contact_email', 'care@ezsafar.in')) ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-sm focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">Email address shown in footer.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Working Hours</label>
                <input type="text" name="settings[working_hours]" value="<?= htmlspecialchars(get_setting('working_hours', '24x7 Roadside Assistance & Hub Desk 08:00 AM - 10:00 PM')) ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-sm focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">Displayed for customer expectations.</p>
            </div>
        </div>

        <div>
            <label class="block font-bold text-slate-600 uppercase mb-1 text-xs">Primary Hub Address</label>
            <textarea name="settings[primary_hub_address]" rows="2" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 font-semibold text-slate-800 text-xs focus:outline-none focus:border-primary"><?= htmlspecialchars(get_setting('primary_hub_address', 'Opp. Railway Station Road, Near Patiala Chowk, Jind, Haryana 126102')) ?></textarea>
            <p class="text-[11px] text-slate-400 mt-1">Full address shown in the footer and reservation vouchers.</p>
        </div>
    </div>

    <!-- TAB 2: SEARCH ENGINE OPTIMIZATION (SEO) -->
    <div id="tab-seo" class="settings-tab-content hidden bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <div>
            <h4 class="text-base font-bold text-slate-800">Search Engine Optimization (SEO) Metadata</h4>
            <p class="text-xs text-slate-500">Configure how EzSafar appears in Google searches and when shared on social media / WhatsApp.</p>
        </div>

        <!-- Google SERP Snippet Live Preview -->
        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
            <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Live Google Search Preview</span>
            <div class="text-[#1a0dab] text-lg font-medium hover:underline cursor-pointer truncate" id="serp-title">
                <?= htmlspecialchars(get_setting('meta_title_default')) ?>
            </div>
            <div class="text-[#006621] text-xs">
                https://ezsafar.in/jind-car-rentals
            </div>
            <div class="text-[#545454] text-xs leading-relaxed line-clamp-2" id="serp-desc">
                <?= htmlspecialchars(get_setting('meta_description_default')) ?>
            </div>
        </div>

        <div class="space-y-4 text-xs">
            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Default SEO Meta Title *</label>
                <input type="text" name="settings[meta_title_default]" id="input-meta-title" value="<?= htmlspecialchars(get_setting('meta_title_default')) ?>" oninput="updateSerpPreview()" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-sm focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">Recommended length: 50–60 characters. Includes target location like Jind, Haryana.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Default SEO Meta Description *</label>
                <textarea name="settings[meta_description_default]" id="input-meta-desc" rows="3" oninput="updateSerpPreview()" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 font-semibold text-slate-800 text-xs focus:outline-none focus:border-primary"><?= htmlspecialchars(get_setting('meta_description_default')) ?></textarea>
                <p class="text-[11px] text-slate-400 mt-1">Recommended length: 140–160 characters. Clearly explain zero deposit self-drive and 140-pt inspected cars.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">SEO Target Keywords (Comma separated)</label>
                <input type="text" name="settings[meta_keywords]" value="<?= htmlspecialchars(get_setting('meta_keywords')) ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-xs focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">e.g. car rental jind, self drive cars haryana, used cars in jind, sell car online jind</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Meta Author Tag</label>
                    <input type="text" name="settings[meta_author]" value="<?= htmlspecialchars(get_setting('meta_author', 'EzSafar Mobility Solutions Pvt. Ltd.')) ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-xs focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Search Engine Robots Directive</label>
                    <select name="settings[meta_robots]" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-xs focus:outline-none">
                        <?php $r = get_setting('meta_robots', 'index, follow'); ?>
                        <option value="index, follow" <?= ($r === 'index, follow') ? 'selected' : '' ?>>index, follow (Allow Search Engines to Index - Recommended)</option>
                        <option value="noindex, nofollow" <?= ($r === 'noindex, nofollow') ? 'selected' : '' ?>>noindex, nofollow (Block Search Engines)</option>
                        <option value="index, nofollow" <?= ($r === 'index, nofollow') ? 'selected' : '' ?>>index, nofollow</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">OpenGraph / WhatsApp Share Image URL</label>
                <input type="url" name="settings[og_image_url]" value="<?= htmlspecialchars(get_setting('og_image_url')) ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-slate-800 text-xs focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">Image displayed when someone shares your link on WhatsApp, Facebook, or Twitter.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Google Analytics Measurement ID</label>
                <input type="text" name="settings[google_analytics_id]" value="<?= htmlspecialchars(get_setting('google_analytics_id')) ?>" placeholder="G-XXXXXXXXXX" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-slate-800 text-xs focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">If provided, the official gtag.js script is automatically injected across all pages.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Custom Head Scripts &amp; Schema Markup</label>
                <textarea name="settings[custom_head_scripts]" rows="3" placeholder="<script>...</script> or <meta ...>" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 font-mono text-slate-800 text-xs focus:outline-none focus:border-primary"><?= htmlspecialchars(get_setting('custom_head_scripts')) ?></textarea>
                <p class="text-[11px] text-slate-400 mt-1">Raw HTML/scripts to output inside the &lt;head&gt; section (Google Search Console verification, Facebook Pixel, etc.).</p>
            </div>
        </div>
    </div>

    <!-- TAB 3: RENTAL & FLEET POLICIES -->
    <div id="tab-pricing" class="settings-tab-content hidden bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <div>
            <h4 class="text-base font-bold text-slate-800">Rental &amp; Pricing Policies</h4>
            <p class="text-xs text-slate-500">Global defaults and surcharges applied across rental calculations.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 text-xs">
            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Default Free KM / Day</label>
                <input type="number" name="settings[default_free_km_per_day]" value="<?= htmlspecialchars(get_setting('default_free_km_per_day', '250')) ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-sm focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">Default free mileage included per rental day.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Default Extra KM Charge (₹/km)</label>
                <input type="number" step="0.5" name="settings[default_extra_km_charge]" value="<?= htmlspecialchars(get_setting('default_extra_km_charge', '12.00')) ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-sm focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">Over-mileage surcharge rate.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Chauffeur Daily Fee (₹/day)</label>
                <input type="number" name="settings[chauffeur_charge_per_day]" value="<?= htmlspecialchars(get_setting('chauffeur_charge_per_day', '600.00')) ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-sm focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">Added when customer picks 'With Chauffeur'.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Doorstep Delivery Charge in Jind (₹)</label>
                <input type="number" name="settings[doorstep_delivery_charge]" value="<?= htmlspecialchars(get_setting('doorstep_delivery_charge', '250.00')) ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-sm focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">Home delivery fee in Jind city.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Airport Drop Surcharge (₹)</label>
                <input type="number" name="settings[airport_drop_charge]" value="<?= htmlspecialchars(get_setting('airport_drop_charge', '1800.00')) ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-sm focus:outline-none focus:border-primary"/>
                <p class="text-[11px] text-slate-400 mt-1">For Delhi IGI / Chandigarh Airport drops.</p>
            </div>
        </div>
    </div>

    <!-- TAB 4: SOCIAL PROFILES -->
    <div id="tab-social" class="settings-tab-content hidden bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <div>
            <h4 class="text-base font-bold text-slate-800">Social Media Links</h4>
            <p class="text-xs text-slate-500">Official company social profiles linked from website pages.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 text-xs">
            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Facebook Page URL</label>
                <input type="url" name="settings[facebook_url]" value="<?= htmlspecialchars(get_setting('facebook_url')) ?>" placeholder="https://facebook.com/..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-xs focus:outline-none focus:border-primary"/>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">Instagram Profile URL</label>
                <input type="url" name="settings[instagram_url]" value="<?= htmlspecialchars(get_setting('instagram_url')) ?>" placeholder="https://instagram.com/..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-xs focus:outline-none focus:border-primary"/>
            </div>

            <div>
                <label class="block font-bold text-slate-600 uppercase mb-1">YouTube Channel URL</label>
                <input type="url" name="settings[youtube_url]" value="<?= htmlspecialchars(get_setting('youtube_url')) ?>" placeholder="https://youtube.com/..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 text-xs focus:outline-none focus:border-primary"/>
            </div>
        </div>
    </div>

    <!-- Save Action Bar Sticky -->
    <div class="sticky bottom-4 z-20 bg-white/95 backdrop-blur-md p-4 rounded-2xl border border-slate-300 shadow-xl flex items-center justify-between gap-4">
        <div class="text-xs text-slate-500 hidden sm:block">
            Changes will take effect immediately across all client-facing pages and SEO search results.
        </div>
        <button type="submit" class="px-6 py-3 rounded-xl bg-primary hover:bg-primary-container text-white font-extrabold text-xs shadow-lg transition-all active:scale-95 flex items-center gap-2 ml-auto">
            <span class="material-symbols-outlined text-base">save</span>
            <span>Save All Settings</span>
        </button>
    </div>
</form>

<script>
function switchSettingsTab(tabName) {
    document.querySelectorAll('.settings-tab-content').forEach(el => el.classList.add('hidden'));
    document.getElementById('tab-' + tabName).classList.remove('hidden');

    ['general', 'seo', 'pricing', 'social'].forEach(name => {
        const btn = document.getElementById('tab-btn-' + name);
        btn.classList.remove('bg-primary', 'text-white', 'shadow-md');
        btn.classList.add('text-slate-600', 'hover:text-slate-900', 'hover:bg-slate-100');
    });

    const activeBtn = document.getElementById('tab-btn-' + tabName);
    activeBtn.classList.add('bg-primary', 'text-white', 'shadow-md');
    activeBtn.classList.remove('text-slate-600', 'hover:text-slate-900', 'hover:bg-slate-100');
}

function updateSerpPreview() {
    const titleVal = document.getElementById('input-meta-title').value;
    const descVal = document.getElementById('input-meta-desc').value;
    document.getElementById('serp-title').innerText = titleVal || 'EzSafar - Premium Car Rentals';
    document.getElementById('serp-desc').innerText = descVal || 'Browse self drive cars in Jind';
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>

