<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'employer';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/EmployerModel.php';
require_once '../model/Close.php';

$profileId = $_SESSION['profile_id'];
$conn      = connect();

$summary       = getEmployerAnalyticsSummary($conn, $profileId);
$appsByJob     = getApplicationsPerJob($conn, $profileId);
$appOverTime   = getApplicationsOverTime($conn, $profileId);

close($conn);
require_once '../view/employer/analytics.php';