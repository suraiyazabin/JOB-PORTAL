<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'admin';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/AdminModel.php';
require_once '../model/Close.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── ADD CATEGORY ──────────────────────────────────────────────
if ($action === 'add') {
    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    if (!$name) {
        $_SESSION['error'] = 'Category name is required.';
        header('Location: AdminCategoryController.php'); exit;
    }
    $conn = connect();
    addCategory($conn, $name, $description);
    close($conn);
    $_SESSION['msg'] = 'Category added.';
    header('Location: AdminCategoryController.php'); exit;
}

// ── EDIT CATEGORY (show form) ─────────────────────────────────
if ($action === 'edit_save') {
    $id          = (int)($_POST['cat_id']     ?? 0);
    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    if (!$id || !$name) {
        $_SESSION['error'] = 'Category name is required.';
        header('Location: AdminCategoryController.php'); exit;
    }
    $conn = connect();
    updateCategory($conn, $id, $name, $description);
    close($conn);
    $_SESSION['msg'] = 'Category updated.';
    header('Location: AdminCategoryController.php'); exit;
}

// ── DELETE CATEGORY ───────────────────────────────────────────
if ($action === 'delete') {
    $id = (int)($_POST['cat_id'] ?? 0);
    if ($id) {
        $conn    = connect();
        $deleted = deleteCategory($conn, $id);
        close($conn);
        if ($deleted) {
            $_SESSION['msg'] = 'Category deleted.';
        } else {
            $_SESSION['error'] = 'Cannot delete: this category has active job postings.';
        }
    }
    header('Location: AdminCategoryController.php'); exit;
}

// ── LOAD VIEW DATA ────────────────────────────────────────────
$conn       = connect();
$categories = getCategoryWithJobCount($conn);

$editCat = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $editCat = getCategoryById($conn, (int)$_GET['id']);
}

close($conn);
require_once '../view/admin/categories.php';