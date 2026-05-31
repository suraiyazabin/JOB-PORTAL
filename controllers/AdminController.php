<?php
session_start();
require_once __DIR__ . '/auth_guard.php';
if ($_SESSION['role'] !== 'admin') { header('Location: AuthController.php'); exit; }
require_once __DIR__ . '/../models/Connect.php';
require_once __DIR__ . '/../models/Close.php';
require_once __DIR__ . '/../models/AdminModel.php';

$action = $_GET['action'] ?? 'dashboard';
$conn   = connect();
$msg    = ''; $error = '';

// ── AJAX: verify/reject/toggle ──
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $aj = $_POST['ajax_action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($aj === 'verify')   { verifyUser($conn, $id);         echo json_encode(['success'=>true,'msg'=>'Account verified.']); }
    elseif ($aj === 'reject')  { rejectUser($conn, $id);      echo json_encode(['success'=>true,'msg'=>'Account rejected.']); }
    elseif ($aj === 'suspend') { toggleUserActive($conn,$id,0); echo json_encode(['success'=>true,'msg'=>'Account suspended.']); }
    elseif ($aj === 'activate'){ toggleUserActive($conn,$id,1); echo json_encode(['success'=>true,'msg'=>'Account activated.']); }
    close($conn); exit;
}

// ── POST ACTIONS ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sub = $_POST['submit_action'] ?? '';

    if ($sub === 'add_category') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if (!$name) $error = 'Category name is required.';
        else { addCategory($conn, $name, $desc) ? $msg = 'Category added.' : $error = 'Failed or already exists.'; }
    }
    elseif ($sub === 'edit_category') {
        $id   = (int)($_POST['cat_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        updateCategory($conn, $id, $name, $desc) ? $msg = 'Category updated.' : $error = 'Update failed.';
    }
    elseif ($sub === 'delete_category') {
        $id = (int)($_POST['cat_id'] ?? 0);
        deleteCategory($conn, $id) ? $msg = 'Category deleted.' : $error = 'Cannot delete — active jobs reference this category.';
    }
    elseif ($sub === 'resolve_complaint') {
        $id   = (int)($_POST['complaint_id'] ?? 0);
        $note = trim($_POST['admin_note'] ?? '');
        resolveComplaint($conn, $id, $note) ? $msg = 'Complaint resolved.' : $error = 'Update failed.';
    }
    elseif ($sub === 'toggle_featured') {
        $id  = (int)($_POST['job_id'] ?? 0);
        $val = (int)($_POST['featured'] ?? 0);
        toggleFeatured($conn, $id, $val) ? $msg = 'Job updated.' : $error = 'Update failed.';
    }
    elseif ($sub === 'delete_job') {
        $id = (int)($_POST['job_id'] ?? 0);
        deleteJob($conn, $id) ? $msg = 'Job removed.' : $error = 'Delete failed.';
    }
    elseif ($sub === 'add_announcement') {
        $title = trim($_POST['title'] ?? '');
        $body  = trim($_POST['body']  ?? '');
        if (!$title || !$body) $error = 'Title and body are required.';
        else addAnnouncement($conn, $_SESSION['user_id'], $title, $body) ? $msg = 'Announcement posted.' : $error = 'Failed.';
    }
    elseif ($sub === 'delete_announcement') {
        $id = (int)($_POST['ann_id'] ?? 0);
        deleteAnnouncement($conn, $id) ? $msg = 'Announcement deleted.' : $error = 'Failed.';
    }

    if ($action !== 'dashboard') {
        header("Location: AdminController.php?action=$action" . ($msg ? "&msg=".urlencode($msg) : "") . ($error ? "&err=".urlencode($error) : ""));
        close($conn); exit;
    }
}

$msg   = $msg   ?: urldecode($_GET['msg'] ?? '');
$error = $error ?: urldecode($_GET['err'] ?? '');

// ── LOAD DATA ──
$search = trim($_GET['search'] ?? '');
$filter = $_GET['filter'] ?? '';

switch ($action) {
    case 'dashboard':
        $stats    = getAdminStats($conn);
        $monthly  = getMonthlyRegistrations($conn);
        $activity = getRecentActivity($conn);
        break;
    case 'employers':
        $employers = getAllEmployers($conn, $search, $filter);
        break;
    case 'recruiters':
        $recruiters = getAllRecruiters($conn, $search, $filter);
        break;
    case 'seekers':
        $seekers = getAllSeekers($conn, $search);
        break;
    case 'categories':
        $categories = getAllCategories($conn);
        break;
    case 'jobs':
        $jobs = getAllJobsAdmin($conn, $search, $filter);
        break;
    case 'complaints':
        $complaints = getAllComplaints($conn, $filter);
        break;
    case 'analytics':
        $analytics = getAnalytics($conn);
        break;
    case 'announcements':
        $announcements = getAllAnnouncements($conn);
        break;
}

close($conn);
include __DIR__ . '/../views/header.php';

switch ($action) {
    case 'dashboard':     include __DIR__ . '/../views/admin/dashboard.php';     break;
    case 'employers':     include __DIR__ . '/../views/admin/employers.php';     break;
    case 'recruiters':    include __DIR__ . '/../views/admin/recruiters.php';    break;
    case 'seekers':       include __DIR__ . '/../views/admin/seekers.php';       break;
    case 'categories':    include __DIR__ . '/../views/admin/categories.php';    break;
    case 'jobs':          include __DIR__ . '/../views/admin/jobs.php';          break;
    case 'complaints':    include __DIR__ . '/../views/admin/complaints.php';    break;
    case 'analytics':     include __DIR__ . '/../views/admin/analytics.php';     break;
    case 'announcements': include __DIR__ . '/../views/admin/announcements.php'; break;
    default:              include __DIR__ . '/../views/admin/dashboard.php';
}

include __DIR__ . '/../views/footer.php';