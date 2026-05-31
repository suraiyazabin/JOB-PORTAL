<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$role = $_SESSION['role'] ?? '';
$name = $_SESSION['name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Job Portal</title>
<link rel="stylesheet" href="../views/external.css">
</head>
<body>
<nav class="navbar">
  <div class="nav-brand">💼 JobPortal</div>
  <div class="nav-links">
    <?php if ($role === 'admin'): ?>
      <a href="AdminController.php?action=dashboard"     <?= ($action??'')==='dashboard'    ?'class="active"':'' ?>>Dashboard</a>
      <a href="AdminController.php?action=employers"     <?= ($action??'')==='employers'    ?'class="active"':'' ?>>Employers</a>
      <a href="AdminController.php?action=recruiters"    <?= ($action??'')==='recruiters'   ?'class="active"':'' ?>>Recruiters</a>
      <a href="AdminController.php?action=seekers"       <?= ($action??'')==='seekers'      ?'class="active"':'' ?>>Seekers</a>
      <a href="AdminController.php?action=categories"    <?= ($action??'')==='categories'   ?'class="active"':'' ?>>Categories</a>
      <a href="AdminController.php?action=jobs"          <?= ($action??'')==='jobs'         ?'class="active"':'' ?>>Jobs</a>
      <a href="AdminController.php?action=complaints"    <?= ($action??'')==='complaints'   ?'class="active"':'' ?>>Complaints</a>
      <a href="AdminController.php?action=analytics"     <?= ($action??'')==='analytics'    ?'class="active"':'' ?>>Analytics</a>
      <a href="AdminController.php?action=announcements" <?= ($action??'')==='announcements'?'class="active"':'' ?>>Announcements</a>
    <?php elseif ($role === 'seeker'): ?>
      <a href="SeekerController.php?action=dashboard"    <?= ($action??'')==='dashboard'   ?'class="active"':'' ?>>Dashboard</a>
      <a href="SeekerController.php?action=jobs"         <?= ($action??'')==='jobs'        ?'class="active"':'' ?>>Browse Jobs</a>
      <a href="SeekerController.php?action=applications" <?= ($action??'')==='applications'?'class="active"':'' ?>>My Applications</a>
      <a href="SeekerController.php?action=saved_jobs"   <?= ($action??'')==='saved_jobs'  ?'class="active"':'' ?>>Saved Jobs</a>
      <a href="SeekerController.php?action=alerts"       <?= ($action??'')==='alerts'      ?'class="active"':'' ?>>Alerts</a>
      <a href="SeekerController.php?action=messages"     <?= ($action??'')==='messages'    ?'class="active"':'' ?>>Messages</a>
      <a href="SeekerController.php?action=profile"      <?= ($action??'')==='profile'     ?'class="active"':'' ?>>Profile</a>
    <?php endif; ?>
    <span class="badge-role"><?= htmlspecialchars(strtoupper($role)) ?></span>
    <span style="color:#b0c4de;font-size:.85rem;padding:0 8px;"><?= htmlspecialchars($name) ?></span>
    <a href="AuthController.php?action=logout" class="nav-logout">Logout</a>
  </div>
</nav>
<div class="container">
