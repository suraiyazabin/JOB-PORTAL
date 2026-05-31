<?php
session_start();
require_once __DIR__ . '/../models/Connect.php';
require_once __DIR__ . '/../models/Close.php';
require_once __DIR__ . '/../models/AuthModel.php';

$action = $_GET['action'] ?? 'login';
$error  = '';

if ($action === 'logout') {
    session_destroy();
    header('Location: AuthController.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = connect();
    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $user  = getUserByEmail($conn, $email);
        if ($user && password_verify($pass, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['pic']     = $user['profile_pic'];
            close($conn);
            if ($user['role'] === 'admin') {
                header('Location: AdminController.php'); exit;
            }
            header('Location: SeekerController.php'); exit;
        } else {
            $error = 'Invalid email or password.';
        }
    } elseif ($action === 'register') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $pass2 = $_POST['password2'] ?? '';
        if (!$name || !$email || !$pass) { $error = 'Please fill all required fields.'; }
        elseif ($pass !== $pass2)         { $error = 'Passwords do not match.'; }
        elseif (strlen($pass) < 6)        { $error = 'Password must be at least 6 characters.'; }
        else {
            $result = registerUser($conn, $name, $email, $pass, $phone, 'seeker');
            if ($result === 'exists') $error = 'An account with this email already exists.';
            elseif ($result) {
                $_SESSION['msg'] = 'Account created! Please log in.';
                header('Location: AuthController.php?action=login'); exit;
            } else $error = 'Registration failed. Please try again.';
        }
    }
    close($conn);
}

$flash = $_SESSION['msg'] ?? ''; unset($_SESSION['msg']);
include __DIR__ . '/../views/auth.php';
