<?php
// Global Helper Functions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!ob_get_level()) {
    ob_start();
}

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';

function sanitize(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function format_rupee($amount): string {
    $amount = (float)$amount;
    // Check if there are non-zero paise
    $hasDecimals = (floor($amount) != $amount);
    if ($hasDecimals) {
        $formatted = number_format($amount, 2, '.', '');
        $parts = explode('.', $formatted);
        $intPart = $parts[0];
        $decimal = '.' . $parts[1];
    } else {
        $intPart = (string)(int)round($amount);
        $decimal = '';
    }

    $last3 = substr($intPart, -3);
    $rest = substr($intPart, 0, -3);
    if ($rest !== '') {
        $last3 = ',' . $last3;
        $rest = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $rest);
    }
    return APP_CURRENCY . ' ' . $rest . $last3 . $decimal;
}

function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'info', 'warning'
        'message' => $message
    ];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function render_flash(): string {
    $flash = get_flash();
    if (!$flash) return '';

    $colors = [
        'success' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
        'error'   => 'bg-rose-50 text-rose-800 border-rose-300',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-300',
        'info'    => 'bg-blue-50 text-blue-800 border-blue-300',
    ];
    $cls = $colors[$flash['type']] ?? $colors['info'];
    $icon = $flash['type'] === 'success' ? 'check_circle' : ($flash['type'] === 'error' ? 'error' : 'info');

    return '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-4">
        <div class="flex items-center gap-3 p-4 rounded-xl border ' . $cls . ' shadow-sm animate-fade-in">
            <span class="material-symbols-outlined text-2xl shrink-0">' . $icon . '</span>
            <div class="font-medium text-sm sm:text-base flex-1">' . htmlspecialchars($flash['message']) . '</div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-current opacity-60 hover:opacity-100 p-1">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
    </div>';
}

function generate_reference_id(string $prefix = 'EZ'): string {
    return $prefix . '-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

function is_admin_logged_in(): bool {
    return isset($_SESSION['admin_user_id']) && !empty($_SESSION['admin_user_id']);
}

function require_admin_auth(): void {
    if (!is_admin_logged_in()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

function get_all_settings(): array {
    static $settingsCache = null;
    if ($settingsCache === null) {
        $settingsCache = [];
        try {
            $db = get_db();
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            while ($row = $stmt->fetch()) {
                $settingsCache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $e) {
            // fallback if DB not initialized
        }
    }
    return $settingsCache;
}

function get_setting(string $key, string $default = ''): string {
    $all = get_all_settings();
    return isset($all[$key]) && $all[$key] !== '' ? $all[$key] : $default;
}

function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text) ?: $text;
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'item-' . time() : $text;
}

