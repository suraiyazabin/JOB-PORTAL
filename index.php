<?php
session_start();
if (!empty($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'seeker';
    if ($role === 'admin') {
        header('Location: controllers/AdminController.php');
    } else {
        header('Location: controllers/SeekerController.php');
    }
} else {
    header('Location: controllers/AuthController.php');
}
exit;