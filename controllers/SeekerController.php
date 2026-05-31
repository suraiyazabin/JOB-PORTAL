<?php
session_start();
require_once __DIR__ . '/auth_guard.php';
if (!in_array($_SESSION['role'], ['seeker'])) { header('Location: AuthController.php'); exit; }
require_once __DIR__ . '/../models/Connect.php';
require_once __DIR__ . '/../models/Close.php';
require_once __DIR__ . '/../models/SeekerModel.php';

$action = $_GET['action'] ?? 'dashboard';
$conn   = connect();
$msg    = ''; $error = '';

// ── AJAX: job filter ──
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' && $action === 'jobs') {
    header('Content-Type: application/json');
    $kw   = trim($_GET['keyword'] ?? '');
    $cat  = $_GET['category'] ?? '';
    $loc  = trim($_GET['location'] ?? '');
    $type = $_GET['job_type'] ?? '';
    $exp  = $_GET['exp_level'] ?? '';
    $smin = $_GET['salary_min'] ?? '';
    $smax = $_GET['salary_max'] ?? '';
    $jobs = getActiveJobs($conn, $kw, $cat, $loc, $type, $exp, $smin, $smax);
    // mark saved
    $user_id = $_SESSION['user_id'];
    foreach ($jobs as &$j) $j['is_saved'] = isSaved($conn, $user_id, $j['id']);
    echo json_encode($jobs);
    close($conn); exit;
}

// ── AJAX: save toggle ──
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' && $action === 'toggle_save') {
    header('Content-Type: application/json');
    $job_id = (int)($_POST['job_id'] ?? 0);
    $result = toggleSaveJob($conn, $_SESSION['user_id'], $job_id);
    echo json_encode(['status' => $result]);
    close($conn); exit;
}

// ── POST ACTIONS ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sub = $_POST['submit_action'] ?? '';

    if ($sub === 'update_profile') {
        $data = [
            'name'              => trim($_POST['name'] ?? ''),
            'phone'             => trim($_POST['phone'] ?? ''),
            'headline'          => trim($_POST['headline'] ?? ''),
            'summary'           => trim($_POST['summary'] ?? ''),
            'skills'            => trim($_POST['skills'] ?? ''),
            'years_experience'  => (int)($_POST['years_experience'] ?? 0),
            'education_level'   => $_POST['education_level'] ?? '',
            'current_salary'    => (float)($_POST['current_salary'] ?? 0),
            'expected_salary'   => (float)($_POST['expected_salary'] ?? 0),
            'preferred_location'=> trim($_POST['preferred_location'] ?? ''),
        ];
        if (!$data['name']) { $error = 'Name is required.'; }
        else { upsertSeekerProfile($conn, $_SESSION['user_id'], $data) ? $msg = 'Profile updated.' : $error = 'Update failed.'; $_SESSION['name'] = $data['name']; }
    }
    elseif ($sub === 'upload_resume') {
        $file = $_FILES['resume'] ?? null;
        if ($file && $file['error'] === 0) {
            if ($file['type'] !== 'application/pdf') { $error = 'Only PDF files allowed.'; }
            elseif ($file['size'] > 5 * 1024 * 1024)  { $error = 'Max file size is 5MB.'; }
            else {
                $dir = __DIR__ . '/../uploads/resumes/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $fname = 'resume_' . $_SESSION['user_id'] . '_' . time() . '.pdf';
                if (move_uploaded_file($file['tmp_name'], $dir . $fname)) {
                    updateResumePath($conn, $_SESSION['user_id'], 'uploads/resumes/' . $fname);
                    $msg = 'Resume uploaded.';
                } else $error = 'Upload failed.';
            }
        } else $error = 'No file selected.';
    }
    elseif ($sub === 'upload_pic') {
        $file = $_FILES['profile_pic'] ?? null;
        if ($file && $file['error'] === 0) {
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            if (!in_array($file['type'], $allowed)) { $error = 'Only image files allowed.'; }
            elseif ($file['size'] > 2 * 1024 * 1024)  { $error = 'Max size is 2MB.'; }
            else {
                $dir = __DIR__ . '/../uploads/profile_pics/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $ext   = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fname = 'pic_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $dir . $fname)) {
                    updateProfilePic($conn, $_SESSION['user_id'], 'uploads/profile_pics/' . $fname);
                    $_SESSION['pic'] = 'uploads/profile_pics/' . $fname;
                    $msg = 'Profile picture updated.';
                } else $error = 'Upload failed.';
            }
        } else $error = 'No file selected.';
    }
    elseif ($sub === 'change_password') {
        $cur  = $_POST['current_password'] ?? '';
        $new  = $_POST['new_password'] ?? '';
        $con  = $_POST['confirm_password'] ?? '';
        $prof = getSeekerProfile($conn, $_SESSION['user_id']);
        if (!password_verify($cur, $prof['password_hash'])) { $error = 'Current password is incorrect.'; }
        elseif ($new !== $con)   { $error = 'New passwords do not match.'; }
        elseif (strlen($new) < 6) { $error = 'Password must be at least 6 characters.'; }
        else { changePassword($conn, $_SESSION['user_id'], password_hash($new, PASSWORD_BCRYPT)) ? $msg = 'Password changed.' : $error = 'Failed.'; }
        $action = 'profile';
    }
    elseif ($sub === 'apply_job') {
        $job_id      = (int)($_POST['job_id'] ?? 0);
        $cover       = trim($_POST['cover_letter'] ?? '');
        $resume_path = '';
        // handle resume upload on apply
        if (!empty($_FILES['apply_resume']['name']) && $_FILES['apply_resume']['error'] === 0) {
            $file = $_FILES['apply_resume'];
            if ($file['type'] === 'application/pdf' && $file['size'] <= 5*1024*1024) {
                $dir = __DIR__ . '/../uploads/resumes/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $fname = 'app_' . $_SESSION['user_id'] . '_' . time() . '.pdf';
                if (move_uploaded_file($file['tmp_name'], $dir . $fname))
                    $resume_path = 'uploads/resumes/' . $fname;
            }
        }
        if (!$resume_path) {
            // fall back to profile resume
            $prof = getSeekerProfile($conn, $_SESSION['user_id']);
            $resume_path = $prof['resume_path'] ?? '';
        }
        $result = applyToJob($conn, $job_id, $_SESSION['user_id'], $cover, $resume_path);
        if ($result === 'duplicate') $error = 'You have already applied to this job.';
        elseif ($result) $msg = 'Application submitted!';
        else $error = 'Application failed.';
        $action = 'job_detail';
        $job_id_param = $job_id;
    }
    elseif ($sub === 'withdraw_application') {
        $app_id = (int)($_POST['app_id'] ?? 0);
        withdrawApplication($conn, $app_id, $_SESSION['user_id']) ? $msg = 'Application withdrawn.' : $error = 'Could not withdraw.';
        $action = 'applications';
    }
    elseif ($sub === 'add_alert') {
        $keyword  = trim($_POST['keyword'] ?? '');
        $cat_id   = (int)($_POST['category_id'] ?? 0) ?: null;
        $location = trim($_POST['location'] ?? '');
        $job_type = $_POST['job_type'] ?? '';
        addAlert($conn, $_SESSION['user_id'], $keyword, $cat_id, $location, $job_type) ? $msg = 'Alert created.' : $error = 'Failed.';
        $action = 'alerts';
    }
    elseif ($sub === 'delete_alert') {
        $id = (int)($_POST['alert_id'] ?? 0);
        deleteAlert($conn, $id, $_SESSION['user_id']) ? $msg = 'Alert deleted.' : $error = 'Failed.';
        $action = 'alerts';
    }
    elseif ($sub === 'send_message') {
        $recipient_id = (int)($_POST['recipient_id'] ?? 0);
        $body         = trim($_POST['body'] ?? '');
        $app_id       = (int)($_POST['app_id'] ?? 0) ?: null;
        if (!$body) $error = 'Message cannot be empty.';
        else sendMessage($conn, $_SESSION['user_id'], $recipient_id, $body, $app_id) ? $msg = 'Message sent.' : $error = 'Failed.';
        $action = 'messages';
    }
    elseif ($sub === 'submit_complaint') {
        $subject_id = (int)($_POST['subject_id'] ?? 0);
        $desc       = trim($_POST['description'] ?? '');
        if (!$desc) $error = 'Please describe your complaint.';
        else submitComplaint($conn, $_SESSION['user_id'], $subject_id, $desc) ? $msg = 'Complaint submitted.' : $error = 'Failed.';
        $action = 'job_detail';
        $job_id_param = (int)($_POST['job_id'] ?? 0);
    }

    $_SESSION['flash_msg']   = $msg;
    $_SESSION['flash_error'] = $error;
    $redirect = "SeekerController.php?action=$action";
    if (!empty($job_id_param)) $redirect .= "&id=$job_id_param";
    header("Location: $redirect"); close($conn); exit;
}

$msg   = $_SESSION['flash_msg']   ?? ''; unset($_SESSION['flash_msg']);
$error = $_SESSION['flash_error'] ?? ''; unset($_SESSION['flash_error']);

// ── LOAD DATA ──
$categories = [];
$cats_res = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
while ($r = mysqli_fetch_assoc($cats_res)) $categories[] = $r;

switch ($action) {
    case 'dashboard':
        $dash = getSeekerDashboard($conn, $_SESSION['user_id']);
        $profile = getSeekerProfile($conn, $_SESSION['user_id']);
        break;
    case 'profile':
        $profile = getSeekerProfile($conn, $_SESSION['user_id']);
        break;
    case 'jobs':
        $kw   = trim($_GET['keyword'] ?? '');
        $cat  = $_GET['category'] ?? '';
        $loc  = trim($_GET['location'] ?? '');
        $type = $_GET['job_type'] ?? '';
        $exp  = $_GET['exp_level'] ?? '';
        $jobs = getActiveJobs($conn, $kw, $cat, $loc, $type, $exp);
        foreach ($jobs as &$j) $j['is_saved'] = isSaved($conn, $_SESSION['user_id'], $j['id']);
        break;
    case 'job_detail':
        $job_id  = (int)($_GET['id'] ?? 0);
        $job     = getJobById($conn, $job_id);
        $applied = hasApplied($conn, $job_id, $_SESSION['user_id']);
        $saved   = isSaved($conn, $_SESSION['user_id'], $job_id);
        $profile = getSeekerProfile($conn, $_SESSION['user_id']);
        break;
    case 'applications':
        $applications = getSeekerApplications($conn, $_SESSION['user_id']);
        break;
    case 'saved_jobs':
        $saved_jobs = getSavedJobs($conn, $_SESSION['user_id']);
        break;
    case 'alerts':
        $alerts = getSeekerAlerts($conn, $_SESSION['user_id']);
        break;
    case 'messages':
        $messages = getSeekerMessages($conn, $_SESSION['user_id']);
        $mark_sender = (int)($_GET['from'] ?? 0);
        if ($mark_sender) markMessagesRead($conn, $_SESSION['user_id'], $mark_sender);
        break;
}

close($conn);
include __DIR__ . '/../views/header.php';

switch ($action) {
    case 'dashboard':   include __DIR__ . '/../views/seeker/dashboard.php';    break;
    case 'profile':     include __DIR__ . '/../views/seeker/profile.php';      break;
    case 'jobs':        include __DIR__ . '/../views/seeker/jobs.php';         break;
    case 'job_detail':  include __DIR__ . '/../views/seeker/job_detail.php';   break;
    case 'applications':include __DIR__ . '/../views/seeker/applications.php'; break;
    case 'saved_jobs':  include __DIR__ . '/../views/seeker/saved_jobs.php';   break;
    case 'alerts':      include __DIR__ . '/../views/seeker/alerts.php';       break;
    case 'messages':    include __DIR__ . '/../views/seeker/messages.php';     break;
    default:            include __DIR__ . '/../views/seeker/dashboard.php';
}

include __DIR__ . '/../views/footer.php';