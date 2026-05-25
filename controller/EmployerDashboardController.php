<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'employer';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/EmployerModel.php';
require_once '../model/Close.php';

$action      = $_POST['action'] ?? '';
$profileId   = $_SESSION['profile_id'];
$userId      = $_SESSION['user_id'];

// ── UPDATE PROFILE ───────────────────────────────────────────
if ($action === 'update_profile') {
    $name        = trim($_POST['name']         ?? '');
    $phone       = trim($_POST['phone']        ?? '');
    $companyName = trim($_POST['company_name'] ?? '');
    $industry    = trim($_POST['industry']     ?? '');
    $companySize = trim($_POST['company_size'] ?? '');
    $description = trim($_POST['description']  ?? '');
    $website     = trim($_POST['website']      ?? '');
    $address     = trim($_POST['address']      ?? '');

    if (!$name || !$companyName || !$address) {
        $_SESSION['error'] = 'Name, Company Name, and Address are required.';
        header('Location: EmployerDashboardController.php?view=profile'); exit;
    }

    $conn    = connect();
    $profile = getEmployerProfile($conn, $profileId);
    $logoPath = $profile['logo_path'];

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $filename = uniqid('logo_', true) . '.' . $ext;
            move_uploaded_file($_FILES['logo']['tmp_name'], '../uploads/logos/' . $filename);
            $logoPath = 'uploads/logos/' . $filename;
        }
    }

    updateEmployerProfile($conn, $profileId, $companyName, $industry, $companySize,
        $description, $website, $address, $logoPath, $name, $phone, $userId);
    close($conn);

    $_SESSION['user_name'] = $name;
    $_SESSION['msg']       = 'Profile updated successfully.';
    header('Location: EmployerDashboardController.php?view=profile'); exit;
}

// ── CHANGE PASSWORD ──────────────────────────────────────────
if ($action === 'change_password') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$current || !$new || !$confirm) {
        $_SESSION['error'] = 'All password fields are required.';
        header('Location: EmployerDashboardController.php?view=profile'); exit;
    }
    if ($new !== $confirm) {
        $_SESSION['error'] = 'New passwords do not match.';
        header('Location: EmployerDashboardController.php?view=profile'); exit;
    }
    if (strlen($new) < 6) {
        $_SESSION['error'] = 'New password must be at least 6 characters.';
        header('Location: EmployerDashboardController.php?view=profile'); exit;
    }

    $conn    = connect();
    $profile = getEmployerProfile($conn, $profileId);

    if (!password_verify($current, $profile['password_hash'])) {
        $_SESSION['error'] = 'Current password is incorrect.';
        close($conn);
        header('Location: EmployerDashboardController.php?view=profile'); exit;
    }

    changePassword($conn, $userId, password_hash($new, PASSWORD_BCRYPT));
    close($conn);

    $_SESSION['msg'] = 'Password changed successfully.';
    header('Location: EmployerDashboardController.php?view=profile'); exit;
}

// ── LOAD VIEW DATA ────────────────────────────────────────────
$view = $_GET['view'] ?? 'dashboard';
$conn = connect();

if ($view === 'profile') {
    $profile = getEmployerProfile($conn, $profileId);
} else {
    $summary      = getEmployerAnalyticsSummary($conn, $profileId);
    $recentJobs   = getJobsByEmployer($conn, $profileId);
    $shortlisted  = getShortlistedByEmployer($conn, $profileId);
}

close($conn);
require_once '../view/employer/dashboard.php';