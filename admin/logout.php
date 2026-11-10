<?php
require_once __DIR__ . '/../includes/functions.php';

unset($_SESSION['admin_user_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_full_name']);
unset($_SESSION['admin_role']);

session_destroy();
header('Location: ' . BASE_URL . '/admin/login.php');
exit;

