<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// $requiredRole is set by the calling controller before including this file.
// Accepted values: 'employer', 'recruiter'
// If not set, any logged-in user is allowed (used for shared pages).

if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'controller/AuthController.php');
    exit;
}

if (isset($requiredRole) && $_SESSION['role'] !== $requiredRole) {
    // Redirect each role to their own dashboard
    if ($_SESSION['role'] === 'employer') {
        header('Location: ' . BASE_URL . 'controller/EmployerDashboardController.php');
    } elseif ($_SESSION['role'] === 'recruiter') {
        header('Location: ' . BASE_URL . 'controller/RecruiterDashboardController.php');
    } else {
        header('Location: ' . BASE_URL . 'controller/AuthController.php');
    }
    exit;
}