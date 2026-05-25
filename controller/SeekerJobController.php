<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'seeker';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/SeekerModel.php';
require_once '../model/EmployerModel.php'; // getAllCategories()
require_once '../model/Close.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

// ── APPLY TO JOB ──────────────────────────────────────────────
if ($action === 'apply') {
    $jobId       = (int)($_POST['job_id']     ?? 0);
    $coverLetter = trim($_POST['cover_letter'] ?? '');

    if (!$jobId) {
        $_SESSION['error'] = 'Invalid job.';
        header('Location: SeekerJobController.php'); exit;
    }

    $conn = connect();

    if (hasApplied($conn, $jobId, $userId)) {
        $_SESSION['error'] = 'You have already applied for this job.';
        close($conn);
        header('Location: SeekerJobController.php?action=detail&id=' . $jobId); exit;
    }

    // Handle resume upload OR use profile resume
    $profile    = getSeekerProfileByUserId($conn, $userId);
    $resumePath = $profile['resume_path'] ?? '';

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
        if ($ext === 'pdf' && $_FILES['resume']['size'] <= 5 * 1024 * 1024) {
            $filename = 'app_' . $userId . '_' . $jobId . '_' . time() . '.pdf';
            move_uploaded_file($_FILES['resume']['tmp_name'], '../uploads/resumes/' . $filename);
            $resumePath = 'uploads/resumes/' . $filename;
        }
    }

    applyToJob($conn, $jobId, $userId, $coverLetter, $resumePath);
    close($conn);

    $_SESSION['msg'] = 'Application submitted successfully!';
    header('Location: SeekerApplicationController.php'); exit;
}

// ── SAVE / UNSAVE JOB (AJAX) ──────────────────────────────────
if ($action === 'toggle_save') {
    $jobId = (int)($_POST['job_id'] ?? 0);
    $conn  = connect();

    if (isSaved($conn, $userId, $jobId)) {
        unsaveJob($conn, $userId, $jobId);
        $saved = false;
    } else {
        saveJob($conn, $userId, $jobId);
        $saved = true;
    }
    close($conn);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'saved' => $saved]);
    exit;
}

// ── JOB DETAIL PAGE ───────────────────────────────────────────
if ($action === 'detail') {
    $jobId = (int)($_GET['id'] ?? 0);
    $conn  = connect();
    $job   = getJobDetail($conn, $jobId);

    if (!$job) {
        close($conn);
        $_SESSION['error'] = 'Job not found or no longer active.';
        header('Location: SeekerJobController.php'); exit;
    }

    $alreadyApplied = hasApplied($conn, $jobId, $userId);
    $alreadySaved   = isSaved($conn, $userId, $jobId);
    $profile        = getSeekerProfileByUserId($conn, $userId);
    close($conn);

    require_once '../view/seeker/job_detail.php';
    exit;
}

// ── DEFAULT: JOB LISTING ──────────────────────────────────────
$keyword    = trim($_GET['keyword']     ?? '');
$categoryId = (int)($_GET['category']  ?? 0);
$location   = trim($_GET['location']   ?? '');
$jobType    = $_GET['job_type']        ?? '';
$expLevel   = $_GET['exp_level']       ?? '';
$salaryMin  = (float)($_GET['sal_min'] ?? 0);
$salaryMax  = (float)($_GET['sal_max'] ?? 0);

$conn       = connect();
$jobs       = getActiveJobs($conn, $keyword, $categoryId, $location, $jobType, $expLevel, $salaryMin, $salaryMax);
$categories = getAllCategories($conn);

// Get saved job IDs for this seeker so we can show "saved" state
$savedRows  = getSavedJobs($conn, $userId);
$savedIds   = array_column($savedRows, 'id');

close($conn);
require_once '../view/seeker/jobs.php';