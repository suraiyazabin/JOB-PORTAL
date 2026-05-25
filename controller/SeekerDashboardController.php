<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'seeker';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/SeekerModel.php';
require_once '../model/EmployerModel.php';  // changePassword()
require_once '../model/Close.php';

$action = $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];

// ── UPDATE PROFILE ───────────────────────────────────────────
if ($action === 'update_profile') {
    $name              = trim($_POST['name']               ?? '');
    $phone             = trim($_POST['phone']              ?? '');
    $headline          = trim($_POST['headline']           ?? '');
    $summary           = trim($_POST['summary']            ?? '');
    $skills            = trim($_POST['skills']             ?? '');
    $yearsExp          = (int)($_POST['years_experience']  ?? 0);
    $educationLevel    = trim($_POST['education_level']    ?? '');
    $currentSalary     = (float)($_POST['current_salary']  ?? 0);
    $expectedSalary    = (float)($_POST['expected_salary'] ?? 0);
    $preferredLocation = trim($_POST['preferred_location'] ?? '');

    if (!$name) {
        $_SESSION['error'] = 'Name is required.';
        header('Location: SeekerDashboardController.php?view=profile'); exit;
    }

    $conn    = connect();
    $profile = getSeekerProfileByUserId($conn, $userId);
    $resumePath = $profile['resume_path'] ?? '';

    // Handle resume upload
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            if ($_FILES['resume']['size'] <= 5 * 1024 * 1024) {
                $filename = 'resume_' . $userId . '_' . time() . '.pdf';
                move_uploaded_file($_FILES['resume']['tmp_name'], '../uploads/resumes/' . $filename);
                $resumePath = 'uploads/resumes/' . $filename;
            } else {
                $_SESSION['error'] = 'Resume must be under 5 MB.';
                close($conn);
                header('Location: SeekerDashboardController.php?view=profile'); exit;
            }
        } else {
            $_SESSION['error'] = 'Resume must be a PDF file.';
            close($conn);
            header('Location: SeekerDashboardController.php?view=profile'); exit;
        }
    }

    // Handle profile picture upload
    $profilePic = '';
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $filename = 'pic_' . $userId . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], '../uploads/profile_pics/' . $filename);
            $profilePic = 'uploads/profile_pics/' . $filename;
        }
    }

    upsertSeekerProfile($conn, $userId, $headline, $summary, $skills, $yearsExp,
        $educationLevel, $currentSalary, $expectedSalary, $preferredLocation, $resumePath);
    updateSeekerUser($conn, $userId, $name, $phone, $profilePic);
    close($conn);

    $_SESSION['user_name'] = $name;
    $_SESSION['msg'] = 'Profile updated successfully.';
    header('Location: SeekerDashboardController.php?view=profile'); exit;
}

// ── CHANGE PASSWORD ──────────────────────────────────────────
if ($action === 'change_password') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$current || !$new || !$confirm) {
        $_SESSION['error'] = 'All fields are required.';
        header('Location: SeekerDashboardController.php?view=profile'); exit;
    }
    if ($new !== $confirm) {
        $_SESSION['error'] = 'New passwords do not match.';
        header('Location: SeekerDashboardController.php?view=profile'); exit;
    }
    if (strlen($new) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters.';
        header('Location: SeekerDashboardController.php?view=profile'); exit;
    }

    $conn    = connect();
    $profile = getSeekerProfileByUserId($conn, $userId);

    // Get full user row for password_hash
    $stmt = mysqli_prepare($conn, "SELECT password_hash FROM users WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $r    = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($r);
    mysqli_stmt_close($stmt);

    if (!password_verify($current, $user['password_hash'])) {
        $_SESSION['error'] = 'Current password is incorrect.';
        close($conn);
        header('Location: SeekerDashboardController.php?view=profile'); exit;
    }

    changePassword($conn, $userId, password_hash($new, PASSWORD_BCRYPT));
    close($conn);
    $_SESSION['msg'] = 'Password changed successfully.';
    header('Location: SeekerDashboardController.php?view=profile'); exit;
}

// ── LOAD VIEW ─────────────────────────────────────────────────
$view = $_GET['view'] ?? 'dashboard';
$conn = connect();

if ($view === 'profile') {
    $profile = getSeekerProfileByUserId($conn, $userId);
} else {
    $stats         = getSeekerStats($conn, $userId);
    $recentApps    = getSeekerApplications($conn, $userId);
    $recentApps    = array_slice($recentApps, 0, 5);
}

close($conn);
require_once '../view/seeker/dashboard.php';