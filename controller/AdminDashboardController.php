<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'admin';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/AdminModel.php';
require_once '../model/Close.php';

$conn    = connect();
$stats   = getAdminDashboardStats($conn);
$pending = getPendingUsers($conn);
close($conn);

require_once '../view/admin/dashboard.php';