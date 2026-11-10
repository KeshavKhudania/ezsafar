<?php
// Site Constants & Global Configuration Template
// Copy this file to 'constants.php' and adjust settings as needed.
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// Environment & Base URL
define('BASE_URL', '/ezsafar');
define('APP_NAME', 'EzSafar');
define('APP_TAGLINE', 'Automotive Mobility Platform');
define('HUB_PRIMARY_CITY', 'Jind');
define('HUB_PRIMARY_STATE', 'Haryana');
define('HUB_LOCATION', 'Jind Central Mobility Hub (Opp. Railway Station Road)');
define('PHONE_SUPPORT', '+91 9729226643');
define('PHONE_WHATSAPP', '919729226643');
define('EMAIL_SUPPORT', 'care@ezsafar.in');
define('APP_CURRENCY', '₹');

// Upload directory
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', BASE_URL . '/assets/uploads/');
