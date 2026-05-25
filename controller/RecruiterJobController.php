<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'recruiter';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/EmployerModel.php';   // getAllCategories
require_once '../model/RecruiterModel.php';
require_once '../model/Close.php';

$action    = $_POST['action'] ?? $_GET['action'] ?? '';
$profileId = $_SESSION['profile_id'];

// ── CREATE JOB ────────────────────────────────────────────────
if ($action === 'save') {
    $clientId       = (int)($_POST['client_id']       ?? 0);
    $categoryId     = (int)($_POST['category_id']     ?? 0);
    $title          = trim($_POST['title']            ?? '');
    $description    = trim($_POST['description']      ?? '');
    $requirements   = trim($_POST['requirements']     ?? '');
    $benefits       = trim($_POST['benefits']         ?? '');
    $salaryMin      = (float)($_POST['salary_min']    ?? 0);
    $salaryMax      = (float)($_POST['salary_max']    ?? 0);
    $location       = trim($_POST['location']         ?? '');
    $jobType        = $_POST['job_type']              ?? 'full-time';
    $expLevel       = $_POST['experience_level']      ?? 'entry';
    $deadline       = $_POST['deadline']              ?? '';
    $status         = $_POST['status']                ?? 'draft';

    if (!$title || !$categoryId || !$location || !$deadline) {
        $_SESSION['error'] = 'Title, category, location, and deadline are required.';
        header('Location: RecruiterJobController.php?action=create&client_id=' . $clientId); exit;
    }

    // Resolve employer_id from the client record
    $conn = connect();
    $client = $clientId ? getClientById($conn, $clientId, $profileId) : null;
    $employerId = $client['employer_id'] ?? null;

    createRecruiterJob($conn, $profileId, $employerId, $categoryId, $title, $description,
        $requirements, $benefits, $salaryMin, $salaryMax, $location, $jobType, $expLevel, $deadline, $status);
    close($conn);

    $_SESSION['msg'] = $status === 'active' ? 'Job published.' : 'Job saved as draft.';
    header('Location: RecruiterJobController.php'); exit;
}

// ── UPDATE JOB ────────────────────────────────────────────────
if ($action === 'update') {
    $jobId      = (int)($_POST['job_id']          ?? 0);
    $categoryId = (int)($_POST['category_id']     ?? 0);
    $title      = trim($_POST['title']            ?? '');
    $description= trim($_POST['description']      ?? '');
    $requirements=trim($_POST['requirements']     ?? '');
    $benefits   = trim($_POST['benefits']         ?? '');
    $salaryMin  = (float)($_POST['salary_min']    ?? 0);
    $salaryMax  = (float)($_POST['salary_max']    ?? 0);
    $location   = trim($_POST['location']         ?? '');
    $jobType    = $_POST['job_type']              ?? 'full-time';
    $expLevel   = $_POST['experience_level']      ?? 'entry';
    $deadline   = $_POST['deadline']              ?? '';
    $status     = $_POST['status']                ?? 'draft';

    if (!$jobId || !$title || !$categoryId || !$location || !$deadline) {
        $_SESSION['error'] = 'Please fill all required fields.';
        header('Location: RecruiterJobController.php?action=edit&id=' . $jobId); exit;
    }

    $conn = connect();
    updateRecruiterJob($conn, $jobId, $profileId, $categoryId, $title, $description,
        $requirements, $benefits, $salaryMin, $salaryMax, $location, $jobType, $expLevel, $deadline, $status);
    close($conn);

    $_SESSION['msg'] = 'Job updated.';
    header('Location: RecruiterJobController.php'); exit;
}

// ── DELETE JOB ────────────────────────────────────────────────
if ($action === 'delete') {
    $jobId = (int)($_POST['job_id'] ?? 0);
    if ($jobId) {
        $conn = connect();
        deleteRecruiterJob($conn, $jobId, $profileId);
        close($conn);
        $_SESSION['msg'] = 'Job deleted.';
    }
    header('Location: RecruiterJobController.php'); exit;
}

// ── AJAX: TOGGLE STATUS ───────────────────────────────────────
if ($action === 'toggle_status') {
    $jobId     = (int)($_POST['job_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';

    if (!in_array($newStatus, ['active', 'closed'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    $conn = connect();
    // Reuse employer toggleJobStatus — works because we pass recruiter's job
    // Use a direct query via recruiter guard
    $stmt = mysqli_prepare($conn, "UPDATE jobs SET status=? WHERE id=? AND recruiter_id=?");
    mysqli_stmt_bind_param($stmt, "sii", $newStatus, $jobId, $profileId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    close($conn);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'status' => $newStatus]);
    exit;
}

// ── LOAD VIEW DATA ────────────────────────────────────────────
$conn = connect();
$filterClientId = (int)($_GET['client_id'] ?? 0);

if ($action === 'create') {
    $categories  = getAllCategories($conn);
    $clients     = getClientsByRecruiter($conn, $profileId);
    $selectedClientId = (int)($_GET['client_id'] ?? 0);
    $editJob = null;
} elseif ($action === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    $editJob = getRecruiterJobById($conn, $id, $profileId);
    if (!$editJob) {
        close($conn); $_SESSION['error'] = 'Job not found.';
        header('Location: RecruiterJobController.php'); exit;
    }
    $categories = getAllCategories($conn);
    $clients    = getClientsByRecruiter($conn, $profileId);
} else {
    $jobs   = getJobsByRecruiter($conn, $profileId);
    // Filter by client if requested
    if ($filterClientId) {
        $jobs = array_filter($jobs, function($j) use ($filterClientId) {
            // client_id not directly on job, filter by employer_id match via client
            return true; // show all, filter handled in view if needed
        });
    }
    $action = 'list';
}

close($conn);
require_once '../view/recruiter/jobs.php';