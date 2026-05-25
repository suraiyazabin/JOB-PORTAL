<?php

session_start();

define('BASE_URL', '/job-portal/');

require_once '../model/Connect.php';
require_once '../model/EmployerModel.php';
require_once '../model/RecruiterModel.php';
require_once '../model/Close.php';

$action = $_POST['action'] ?? $_GET['action'] ?? 'login';

// ── LOGOUT ──────────────────────────────────────────────────
if ($action === 'logout') {
    session_destroy();
    header('Location: AuthController.php');
    exit;
}

// ── EMPLOYER REGISTER ────────────────────────────────────────
if ($action === 'employer_register_save') {
    $name        = trim($_POST['name']         ?? '');
    $email       = trim($_POST['email']        ?? '');
    $phone       = trim($_POST['phone']        ?? '');
    $password    = $_POST['password']          ?? '';
    $confirm     = $_POST['confirm_password']  ?? '';
    $companyName = trim($_POST['company_name'] ?? '');
    $industry    = trim($_POST['industry']     ?? '');
    $companySize = trim($_POST['company_size'] ?? '');
    $description = trim($_POST['description']  ?? '');
    $website     = trim($_POST['website']      ?? '');
    $address     = trim($_POST['address']      ?? '');

    if (!$name || !$email || !$password || !$companyName || !$address) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        header('Location: AuthController.php?action=employer_register'); exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email address.';
        header('Location: AuthController.php?action=employer_register'); exit;
    }
    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters.';
        header('Location: AuthController.php?action=employer_register'); exit;
    }
    if ($password !== $confirm) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: AuthController.php?action=employer_register'); exit;
    }

    $conn = connect();

    if (emailExists($conn, $email)) {
        $_SESSION['error'] = 'This email is already registered.';
        close($conn);
        header('Location: AuthController.php?action=employer_register'); exit;
    }

    // Handle logo upload
    $logoPath = '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $filename = uniqid('logo_', true) . '.' . $ext;
            move_uploaded_file($_FILES['logo']['tmp_name'], '../uploads/logos/' . $filename);
            $logoPath = 'uploads/logos/' . $filename;
        }
    }

    $hash   = password_hash($password, PASSWORD_BCRYPT);
    $userId = insertUser($conn, $name, $email, $hash, $phone, 'employer');
    insertEmployerProfile($conn, $userId, $companyName, $industry, $companySize, $description, $website, $address, $logoPath);
    close($conn);

    $_SESSION['msg'] = 'Registration submitted! Please wait for admin verification before logging in.';
    header('Location: AuthController.php'); exit;
}

// ── RECRUITER REGISTER ───────────────────────────────────────
if ($action === 'recruiter_register_save') {
    $name           = trim($_POST['name']           ?? '');
    $email          = trim($_POST['email']          ?? '');
    $phone          = trim($_POST['phone']          ?? '');
    $password       = $_POST['password']            ?? '';
    $confirm        = $_POST['confirm_password']    ?? '';
    $agencyName     = trim($_POST['agency_name']    ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $description    = trim($_POST['description']    ?? '');
    $website        = trim($_POST['website']        ?? '');

    if (!$name || !$email || !$password || !$agencyName) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        header('Location: AuthController.php?action=recruiter_register'); exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email address.';
        header('Location: AuthController.php?action=recruiter_register'); exit;
    }
    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters.';
        header('Location: AuthController.php?action=recruiter_register'); exit;
    }
    if ($password !== $confirm) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: AuthController.php?action=recruiter_register'); exit;
    }

    $conn = connect();

    if (emailExists($conn, $email)) {
        $_SESSION['error'] = 'This email is already registered.';
        close($conn);
        header('Location: AuthController.php?action=recruiter_register'); exit;
    }

    $hash   = password_hash($password, PASSWORD_BCRYPT);
    $userId = insertUser($conn, $name, $email, $hash, $phone, 'recruiter');
    insertRecruiterProfile($conn, $userId, $agencyName, $specialization, $description, $website);
    close($conn);

    $_SESSION['msg'] = 'Registration submitted! Please wait for admin verification before logging in.';
    header('Location: AuthController.php'); exit;
}

// ── LOGIN (shared for employer + recruiter) ──────────────────
if ($action === 'login_save') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $role     = $_POST['role']          ?? '';   // 'employer' or 'recruiter'

    if (!$email || !$password || !$role) {
        $_SESSION['error'] = 'Please fill in all fields.';
        header('Location: AuthController.php'); exit;
    }

    $conn = connect();
    $user = getUserByEmailAndRole($conn, $email, $role);
    close($conn);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $_SESSION['error'] = 'Invalid email or password.';
        header('Location: AuthController.php'); exit;
    }
    if (!$user['is_verified']) {
        $_SESSION['error'] = 'Your account is pending admin verification.';
        header('Location: AuthController.php'); exit;
    }
    if (!$user['is_active']) {
        $_SESSION['error'] = 'Your account has been suspended. Please contact support.';
        header('Location: AuthController.php'); exit;
    }

    // Set session
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['profile_id'] = $user['profile_id'];  // employer_profiles.id or recruiter_profiles.id

    if ($role === 'employer') {
        header('Location: EmployerDashboardController.php'); exit;
    } else {
        header('Location: RecruiterDashboardController.php'); exit;
    }
}

// ── SHOW VIEW ────────────────────────────────────────────────
require_once '../view/auth.php';