<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'recruiter';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/RecruiterModel.php';
require_once '../model/Close.php';

$action    = $_POST['action'] ?? $_GET['action'] ?? '';
$profileId = $_SESSION['profile_id'];

// ── AJAX: UPDATE PIPELINE STATUS ─────────────────────────────
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
    updatePipelineStatus($conn, $appId, $newStatus, $profileId);
    close($conn);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'status' => $newStatus]);
    exit;
}

// ── CLIENT REPORT VIEW ────────────────────────────────────────
if ($action === 'client_report') {
    $clientId = (int)($_GET['client_id'] ?? 0);
    if (!$clientId) {
        header('Location: PipelineController.php'); exit;
    }
    $conn     = connect();
    $client   = getClientById($conn, $clientId, $profileId);
    if (!$client) {
        close($conn);
        $_SESSION['error'] = 'Client not found.';
        header('Location: PipelineController.php'); exit;
    }
    $report   = getClientReport($conn, $profileId, $clientId);
    $clients  = getClientsByRecruiter($conn, $profileId);
    $analytics = getRecruiterAnalytics($conn, $profileId);
    close($conn);
    require_once '../view/recruiter/pipeline.php';
    exit;
}

// ── DEFAULT: FULL PIPELINE ────────────────────────────────────
$filterStatus = $_GET['status'] ?? '';
$conn = connect();

$pipeline  = getPipelineByRecruiter($conn, $profileId, $filterStatus);
$analytics = getRecruiterAnalytics($conn, $profileId);
$clients   = getClientsByRecruiter($conn, $profileId);

close($conn);
$action = 'pipeline';
require_once '../view/recruiter/pipeline.php';