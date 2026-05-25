<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$flashMsg   = $_SESSION['msg']   ?? '';
$flashError = $_SESSION['error'] ?? '';
$_SESSION['msg']   = '';
$_SESSION['error'] = '';

$pageTitle  = $pageTitle  ?? 'Job Portal';
$activePage = $activePage ?? '';
$role       = $_SESSION['role'] ?? '';

function navClass($page) {
    global $activePage;
    return $activePage === $page ? ' class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle); ?> — Job Portal</title>

    <link rel="stylesheet" href="../view/css/style.css">

    <!-- ✅ ICON PACK (Bootstrap Icons) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<nav class="navbar">

    <!-- LOGO UPDATED -->
    <div class="nav-brand">
        <i class="bi bi-briefcase-fill"></i>
        Job Portal
    </div>

    <div class="nav-links">

        <?php if ($role === 'employer'): ?>
            <a href="EmployerDashboardController.php"<?php echo navClass('dashboard'); ?>>
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a href="JobController.php"<?php echo navClass('jobs'); ?>>
                <i class="bi bi-briefcase"></i> My Jobs
            </a>

            <a href="ApplicantController.php"<?php echo navClass('applicants'); ?>>
                <i class="bi bi-people"></i> Applicants
            </a>

            <a href="AnalyticsController.php"<?php echo navClass('analytics'); ?>>
                <i class="bi bi-graph-up"></i> Analytics
            </a>

            <a href="EmployerDashboardController.php?view=profile"<?php echo navClass('profile'); ?>>
                <i class="bi bi-person-circle"></i>
                <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Profile'); ?>
            </a>

        <?php elseif ($role === 'recruiter'): ?>
            <a href="RecruiterDashboardController.php"<?php echo navClass('dashboard'); ?>>
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a href="ClientController.php"<?php echo navClass('clients'); ?>>
                <i class="bi bi-building"></i> Clients
            </a>

            <a href="RecruiterJobController.php"<?php echo navClass('rec_jobs'); ?>>
                <i class="bi bi-briefcase"></i> Jobs
            </a>

            <a href="CandidateController.php"<?php echo navClass('candidates'); ?>>
                <i class="bi bi-person-lines-fill"></i> Candidates
            </a>

            <a href="PipelineController.php"<?php echo navClass('pipeline'); ?>>
                <i class="bi bi-diagram-3"></i> Pipeline
            </a>

            <a href="RecruiterDashboardController.php?view=profile"<?php echo navClass('profile'); ?>>
                <i class="bi bi-person-circle"></i>
                <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Profile'); ?>
            </a>

        <?php elseif ($role === 'admin'): ?>
            <a href="AdminDashboardController.php"<?php echo navClass('dashboard'); ?>>
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a href="AdminUserController.php?tab=pending"<?php echo navClass('users'); ?>>
                <i class="bi bi-hourglass-split"></i> Approvals
            </a>

            <a href="AdminUserController.php?tab=employers"<?php echo navClass('employers'); ?>>
                <i class="bi bi-building"></i> Employers
            </a>

            <a href="AdminUserController.php?tab=recruiters"<?php echo navClass('recruiters'); ?>>
                <i class="bi bi-person-badge"></i> Recruiters
            </a>

            <a href="AdminUserController.php?tab=seekers"<?php echo navClass('seekers'); ?>>
                <i class="bi bi-person"></i> Seekers
            </a>

            <a href="AdminCategoryController.php"<?php echo navClass('categories'); ?>>
                <i class="bi bi-tags"></i> Categories
            </a>

            <a href="AdminComplaintController.php"<?php echo navClass('complaints'); ?>>
                <i class="bi bi-exclamation-triangle"></i> Complaints
            </a>

            <a href="AdminReportsController.php"<?php echo navClass('reports'); ?>>
                <i class="bi bi-bar-chart"></i> Reports
            </a>

            <span style="color:#c8d8f0;padding:8px 10px;font-size:.85rem">
                <i class="bi bi-shield-lock"></i>
                <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>
            </span>

        <?php elseif ($role === 'seeker'): ?>
            <a href="SeekerDashboardController.php"<?php echo navClass('dashboard'); ?>>
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a href="SeekerJobController.php"<?php echo navClass('jobs'); ?>>
                <i class="bi bi-search"></i> Browse Jobs
            </a>

            <a href="SeekerApplicationController.php"<?php echo navClass('applications'); ?>>
                <i class="bi bi-file-earmark-text"></i> My Applications
            </a>

            <a href="SeekerApplicationController.php?view=saved"<?php echo navClass('saved'); ?>>
                <i class="bi bi-bookmark"></i> Saved Jobs
            </a>

            <a href="SeekerDashboardController.php?view=profile"<?php echo navClass('profile'); ?>>
                <i class="bi bi-person-circle"></i>
                <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Profile'); ?>
            </a>

        <?php endif; ?>

        <a href="AuthController.php?action=logout" class="nav-logout">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</nav>

<div class="container">

<?php if ($flashMsg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($flashMsg); ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($flashError); ?></div>
<?php endif; ?>