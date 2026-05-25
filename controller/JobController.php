<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'employer';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/EmployerModel.php';
require_once '../model/Close.php';

$action    = $_POST['action'] ?? $_GET['action'] ?? '';
$profileId = $_SESSION['profile_id'];

// ── CREATE JOB ────────────────────────────────────────────────
if ($action === 'save') {
    $categoryId     = (int)($_POST['category_id']     ?? 0);
    $title          = trim($_POST['title']            ?? '');
    $description    = trim($_POST['description']      ?? '');
    $requirements   = trim($_POST['requirements']     ?? '');
    $benefits       = trim($_POST['benefits']         ?? '');
    $salaryMin      = (float)($_POST['salary_min']    ?? 0);
    $salaryMax      = (float)($_POST['salary_max']    ?? 0);
    $location       = trim($_POST['location']         ?? '');
    $jobType        = $_POST['job_type']              ?? 'full-time';
    $experienceLevel= $_POST['experience_level']      ?? 'entry';
    $deadline       = $_POST['deadline']              ?? '';
    $status         = $_POST['status']                ?? 'draft';

    if (!$title || !$categoryId || !$location || !$deadline) {
        $_SESSION['error'] = 'Title, category, location, and deadline are required.';
        header('Location: JobController.php?action=create'); exit;
    }
    if (!in_array($status, ['active', 'draft'])) {
        $status = 'draft';
    }

    $conn = connect();
    createJob($conn, $profileId, $categoryId, $title, $description, $requirements,
        $benefits, $salaryMin, $salaryMax, $location, $jobType, $experienceLevel, $deadline, $status);
    close($conn);

    $_SESSION['msg'] = $status === 'active'
        ? 'Job published successfully.'
        : 'Job saved as draft.';
    header('Location: JobController.php'); exit;
}

// ── UPDATE JOB ────────────────────────────────────────────────
if ($action === 'update') {
    $jobId          = (int)($_POST['job_id']          ?? 0);
    $categoryId     = (int)($_POST['category_id']     ?? 0);
    $title          = trim($_POST['title']            ?? '');
    $description    = trim($_POST['description']      ?? '');
    $requirements   = trim($_POST['requirements']     ?? '');
    $benefits       = trim($_POST['benefits']         ?? '');
    $salaryMin      = (float)($_POST['salary_min']    ?? 0);
    $salaryMax      = (float)($_POST['salary_max']    ?? 0);
    $location       = trim($_POST['location']         ?? '');
    $jobType        = $_POST['job_type']              ?? 'full-time';
    $experienceLevel= $_POST['experience_level']      ?? 'entry';
    $deadline       = $_POST['deadline']              ?? '';
    $status         = $_POST['status']                ?? 'draft';

    if (!$jobId || !$title || !$categoryId || !$location || !$deadline) {
        $_SESSION['error'] = 'Title, category, location, and deadline are required.';
        header('Location: JobController.php?action=edit&id=' . $jobId); exit;
    }

    $conn = connect();
    updateJob($conn, $jobId, $profileId, $categoryId, $title, $description, $requirements,
        $benefits, $salaryMin, $salaryMax, $location, $jobType, $experienceLevel, $deadline, $status);
    close($conn);

    $_SESSION['msg'] = 'Job updated successfully.';
    header('Location: JobController.php'); exit;
}

// ── DELETE JOB ────────────────────────────────────────────────
if ($action === 'delete') {
    $jobId = (int)($_POST['job_id'] ?? 0);
    if ($jobId) {
        $conn = connect();
        if (jobHasApplications($conn, $jobId)) {
            $_SESSION['error'] = 'Cannot delete a job that already has applications.';
        } else {
            deleteJob($conn, $jobId, $profileId);
            $_SESSION['msg'] = 'Job deleted.';
        }
        close($conn);
    }
    header('Location: JobController.php'); exit;
}

// ── AJAX: TOGGLE STATUS ───────────────────────────────────────
if ($action === 'toggle_status') {
    $jobId     = (int)($_POST['job_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';

    if (!in_array($newStatus, ['active', 'closed'])) {
        echo json_encode(['success' => false]);
        exit;
    }

    $conn = connect();
    toggleJobStatus($conn, $jobId, $profileId, $newStatus);
    close($conn);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'status' => $newStatus]);
    exit;
}

// ── LOAD VIEW DATA ────────────────────────────────────────────
$conn = connect();

if ($action === 'create') {
    $categories = getAllCategories($conn);
    $editJob    = null;
} elseif ($action === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    $editJob = getJobById($conn, $id, $profileId);
    if (!$editJob) {
        close($conn);
        $_SESSION['error'] = 'Job not found.';
        header('Location: JobController.php'); exit;
    }
    $categories = getAllCategories($conn);
} else {
    $jobs = getJobsByEmployer($conn, $profileId);
    $action = 'list';
}

close($conn);
require_once '../view/employer/jobs.php';