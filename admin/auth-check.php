<?php
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin_logged_in()) {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

