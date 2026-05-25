<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'admin';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/AdminModel.php';
require_once '../model/Close.php';

$action = $_POST['action'] ?? '';

// ── RESOLVE COMPLAINT ─────────────────────────────────────────
if ($action === 'resolve') {
    $id        = (int)($_POST['complaint_id'] ?? 0);
    $adminNote = trim($_POST['admin_note']    ?? '');
    if ($id) {
        $conn = connect();
        resolveComplaint($conn, $id, $adminNote);
        close($conn);
        $_SESSION['msg'] = 'Complaint marked as resolved.';
    }
    header('Location: AdminComplaintController.php'); exit;
}

$conn       = connect();
$complaints = getAllComplaints($conn);
close($conn);

require_once '../view/admin/complaints.php';
