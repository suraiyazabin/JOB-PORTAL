<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'seeker';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/SeekerModel.php';
require_once '../model/Close.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

// ── WITHDRAW APPLICATION ──────────────────────────────────────
if ($action === 'withdraw') {
    $appId = (int)($_POST['app_id'] ?? 0);
    if ($appId) {
        $conn    = connect();
        $success = withdrawApplication($conn, $appId, $userId);
        close($conn);
        if ($success) {
            $_SESSION['msg'] = 'Application withdrawn.';
        } else {
            $_SESSION['error'] = 'Cannot withdraw — application is already being reviewed.';
        }
    }
    header('Location: SeekerApplicationController.php'); exit;
}

// ── UNSAVE JOB ────────────────────────────────────────────────
if ($action === 'unsave') {
    $jobId = (int)($_POST['job_id'] ?? 0);
    if ($jobId) {
        $conn = connect();
        unsaveJob($conn, $userId, $jobId);
        close($conn);
        $_SESSION['msg'] = 'Job removed from saved list.';
    }
    header('Location: SeekerApplicationController.php?view=saved'); exit;
}

// ── LOAD VIEW ─────────────────────────────────────────────────
$view = $_GET['view'] ?? 'applications';
$conn = connect();

if ($view === 'saved') {
    $savedJobs = getSavedJobs($conn, $userId);
} else {
    $applications = getSeekerApplications($conn, $userId);
    $view = 'applications';
}

close($conn);
require_once '../view/seeker/applications.php';