<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'admin';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/AdminModel.php';
require_once '../model/Close.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── APPROVE USER ──────────────────────────────────────────────
if ($action === 'approve') {
    $userId = (int)($_POST['user_id'] ?? 0);
    if ($userId) {
        $conn = connect();
        approveUser($conn, $userId);
        close($conn);
        $_SESSION['msg'] = 'Account approved successfully.';
    }
    header('Location: AdminUserController.php?tab=pending'); exit;
}

// ── REJECT USER ───────────────────────────────────────────────
if ($action === 'reject') {
    $userId = (int)($_POST['user_id'] ?? 0);
    if ($userId) {
        $conn = connect();
        rejectUser($conn, $userId);
        close($conn);
        $_SESSION['msg'] = 'Account rejected.';
    }
    header('Location: AdminUserController.php?tab=pending'); exit;
}

// ── SUSPEND / REACTIVATE ──────────────────────────────────────
if ($action === 'toggle_active') {
    $userId    = (int)($_POST['user_id']  ?? 0);
    $newActive = (int)($_POST['is_active'] ?? 0);
    $tab       = $_POST['tab'] ?? 'employers';
    if ($userId) {
        $conn = connect();
        setUserActive($conn, $userId, $newActive);
        close($conn);
        $_SESSION['msg'] = $newActive ? 'Account reactivated.' : 'Account suspended.';
    }
    header('Location: AdminUserController.php?tab=' . $tab); exit;
}

// ── LOAD VIEW DATA ────────────────────────────────────────────
$tab    = $_GET['tab'] ?? 'pending';
$search = trim($_GET['search'] ?? '');
$conn   = connect();

switch ($tab) {
    case 'employers':
        $users = getAllUsersByRole($conn, 'employer');
        break;
    case 'recruiters':
        $users = getAllUsersByRole($conn, 'recruiter');
        break;
    case 'seekers':
        $users = getAllSeekers($conn, $search);
        break;
    default: // pending
        $users = getPendingUsers($conn);
        $tab   = 'pending';
}

close($conn);
require_once '../view/admin/users.php';