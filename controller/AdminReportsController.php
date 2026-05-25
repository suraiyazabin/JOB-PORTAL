<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'admin';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/AdminModel.php';
require_once '../model/Close.php';

$conn = connect();

$jobsByCategory    = getJobsByCategory($conn);
$registrations     = getNewRegistrationsPerMonth($conn);
$topEmployers      = getTopEmployers($conn);
$activeRecruiters  = getMostActiveRecruiters($conn);
$popularLocations  = getPopularLocations($conn);
$stats             = getAdminDashboardStats($conn);

close($conn);

require_once '../view/admin/reports.php';