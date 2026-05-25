<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'employer';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/EmployerModel.php';
require_once '../model/Close.php';

$action    = $_POST['action'] ?? $_GET['action'] ?? '';
$profileId = $_SESSION['profile_id'];
$userId    = $_SESSION['user_id'];

// ── AJAX: UPDATE APPLICATION STATUS ──────────────────────────
if ($action === 'update_status_ajax') {
    $appId     = (int)($_POST['app_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    $allowed   = ['reviewed', 'shortlisted', 'interview', 'rejected'];

    if (!$appId || !in_array($newStatus, $allowed)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'msg' => 'Invalid input']);
        exit;
    }

    $conn = connect();
    updateApplicationStatus($conn, $appId, $newStatus, $profileId);
    close($conn);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'status' => $newStatus]);
    exit;
}

// ── SEND MESSAGE TO APPLICANT ─────────────────────────────────
if ($action === 'send_message') {
    $appId       = (int)($_POST['app_id']       ?? 0);
    $recipientId = (int)($_POST['recipient_id'] ?? 0);
    $body        = trim($_POST['body']          ?? '');

    if (!$appId || !$recipientId || !$body) {
        $_SESSION['error'] = 'Message cannot be empty.';
        header('Location: ApplicantController.php?action=view&id=' . $appId); exit;
    }

    $conn = connect();
    sendMessage($conn, $userId, $recipientId, $appId, $body);
    close($conn);

    $_SESSION['msg'] = 'Message sent successfully.';
    header('Location: ApplicantController.php?action=view&id=' . $appId); exit;
}

// ── SUBMIT COMPLAINT ──────────────────────────────────────────
if ($action === 'submit_complaint') {
    $subjectId   = (int)($_POST['subject_id']  ?? 0);
    $description = trim($_POST['description']  ?? '');

    if (!$subjectId || !$description) {
        $_SESSION['error'] = 'Please fill in all complaint fields.';
        header('Location: ApplicantController.php?action=view&id=' . $_POST['app_id']); exit;
    }

    $conn = connect();
    submitComplaint($conn, $userId, $subjectId, $description);
    close($conn);

    $_SESSION['msg'] = 'Complaint submitted to admin.';
    header('Location: ApplicantController.php?job_id=' . ($_POST['job_id'] ?? '')); exit;
}

// ── LOAD VIEW DATA ────────────────────────────────────────────
$conn = connect();

if ($action === 'view') {
    // View individual applicant detail
    $appId = (int)($_GET['id'] ?? 0);
    $app   = getApplicationById($conn, $appId, $profileId);
    if (!$app) {
        close($conn);
        $_SESSION['error'] = 'Application not found.';
        header('Location: ApplicantController.php'); exit;
    }
    close($conn);
    require_once '../view/employer/applicant_detail.php';
    exit;
}

// Default: show applicant list for a specific job (or all jobs)
$jobId = (int)($_GET['job_id'] ?? 0);

if ($jobId) {
    $applications = getApplicationsByJob($conn, $jobId, $profileId);
    // Get job title for heading
    $job = getJobById($conn, $jobId, $profileId);
} else {
    // No job filter: show shortlisted across all jobs
    $applications = getShortlistedByEmployer($conn, $profileId);
    $job = null;
}

close($conn);
require_once '../view/employer/applicants.php';