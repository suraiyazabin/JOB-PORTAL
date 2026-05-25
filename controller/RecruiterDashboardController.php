<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'recruiter';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/EmployerModel.php';
require_once '../model/RecruiterModel.php';
require_once '../model/Close.php';

$action    = $_POST['action'] ?? '';
$profileId = $_SESSION['profile_id'];
$userId    = $_SESSION['user_id'];

// ── UPDATE PROFILE ───────────────────────────────────────────
if ($action === 'update_profile') {
    $name           = trim($_POST['name']           ?? '');
    $phone          = trim($_POST['phone']          ?? '');
    $agencyName     = trim($_POST['agency_name']    ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $description    = trim($_POST['description']    ?? '');
    $website        = trim($_POST['website']        ?? '');

    if (!$name || !$agencyName) {
        $_SESSION['error'] = 'Name and Agency Name are required.';
        header('Location: RecruiterDashboardController.php?view=profile'); exit;
    }

    $conn = connect();
    updateRecruiterProfile($conn, $profileId, $agencyName, $specialization,
        $description, $website, $name, $phone, $userId);
    close($conn);

    $_SESSION['user_name'] = $name;
    $_SESSION['msg']       = 'Profile updated successfully.';
    header('Location: RecruiterDashboardController.php?view=profile'); exit;
}

// ── CHANGE PASSWORD ──────────────────────────────────────────
if ($action === 'change_password') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$current || !$new || !$confirm) {
        $_SESSION['error'] = 'All password fields are required.';
        header('Location: RecruiterDashboardController.php?view=profile'); exit;
    }
    if ($new !== $confirm) {
        $_SESSION['error'] = 'New passwords do not match.';
        header('Location: RecruiterDashboardController.php?view=profile'); exit;
    }
    if (strlen($new) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters.';
        header('Location: RecruiterDashboardController.php?view=profile'); exit;
    }

    $conn    = connect();
    $profile = getRecruiterProfile($conn, $profileId);

    if (!password_verify($current, $profile['password_hash'])) {
        $_SESSION['error'] = 'Current password is incorrect.';
        close($conn);
        header('Location: RecruiterDashboardController.php?view=profile'); exit;
    }

    changePassword($conn, $userId, password_hash($new, PASSWORD_BCRYPT));
    close($conn);

    $_SESSION['msg'] = 'Password changed successfully.';
    header('Location: RecruiterDashboardController.php?view=profile'); exit;
}

// ── LOAD VIEW DATA ─────────────────────────────────────────
$view = $_GET['view'] ?? 'dashboard';
$conn = connect();

if ($view === 'profile') {
    $profile = getRecruiterProfile($conn, $profileId);
    // Add password_hash to profile for change-password check
} else {
    $analytics  = getRecruiterAnalytics($conn, $profileId);
    $clients    = getClientsByRecruiter($conn, $profileId);
    $recentJobs = getJobsByRecruiter($conn, $profileId);
    $pipeline   = getPipelineByRecruiter($conn, $profileId);
}

close($conn);
require_once '../view/recruiter/dashboard.php';