<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'recruiter';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/RecruiterModel.php';
require_once '../model/Close.php';

$action    = $_POST['action'] ?? $_GET['action'] ?? '';
$profileId = $_SESSION['profile_id'];

// ── ADD CLIENT ────────────────────────────────────────────────
if ($action === 'add_save') {
    $employerId           = (int)($_POST['employer_id']            ?? 0);
    $companyNameOverride  = trim($_POST['company_name_override']   ?? '');

    // Must have either a registered employer_id OR a custom name
    if (!$employerId && !$companyNameOverride) {
        $_SESSION['error'] = 'Please select a registered employer OR enter a custom company name.';
        header('Location: ClientController.php?action=add'); exit;
    }

    $conn = connect();
    $empId = $employerId ?: null;
    addClient($conn, $profileId, $empId, $companyNameOverride);
    close($conn);

    $_SESSION['msg'] = 'Client added successfully.';
    header('Location: ClientController.php'); exit;
}

// ── DELETE CLIENT ─────────────────────────────────────────────
if ($action === 'delete') {
    $clientId = (int)($_POST['client_id'] ?? 0);
    if ($clientId) {
        $conn = connect();
        deleteClient($conn, $clientId, $profileId);
        close($conn);
        $_SESSION['msg'] = 'Client removed.';
    }
    header('Location: ClientController.php'); exit;
}

// ── LOAD VIEW DATA ────────────────────────────────────────────
$conn = connect();

if ($action === 'add') {
    $employers = getAllVerifiedEmployers($conn);
} else {
    $clients = getClientsByRecruiter($conn, $profileId);
    $action  = 'list';
}

close($conn);
require_once '../view/recruiter/clients.php';