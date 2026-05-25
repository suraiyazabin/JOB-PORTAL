<?php

define('BASE_URL', '/job-portal/');
$requiredRole = 'recruiter';
require_once 'auth_guard.php';
require_once '../model/Connect.php';
require_once '../model/RecruiterModel.php';
require_once '../model/Close.php';

$action    = $_POST['action'] ?? $_GET['action'] ?? '';
$profileId = $_SESSION['profile_id'];

// ── AJAX: SEARCH SEEKERS ──────────────────────────────────────
if ($action === 'search_ajax') {
    $keyword  = trim($_GET['keyword']  ?? '');
    $location = trim($_GET['location'] ?? '');
    $expLevel = trim($_GET['exp']      ?? '');
    $eduLevel = trim($_GET['edu']      ?? '');

    $conn    = connect();
    $seekers = searchSeekers($conn, $keyword, $location, $expLevel, $eduLevel);
    close($conn);

    header('Content-Type: application/json');
    echo json_encode($seekers);
    exit;
}

// ── SEND OUTREACH ─────────────────────────────────────────────
if ($action === 'outreach_save') {
    $seekerId = (int)($_POST['seeker_id'] ?? 0);
    $jobId    = (int)($_POST['job_id']    ?? 0) ?: null;
    $message  = trim($_POST['message']   ?? '');

    if (!$seekerId || !$message) {
        $_SESSION['error'] = 'Please fill in the message.';
        header('Location: CandidateController.php?action=outreach&id=' . $seekerId); exit;
    }

    $conn = connect();
    sendOutreach($conn, $profileId, $seekerId, $jobId, $message);
    close($conn);

    $_SESSION['msg'] = 'Outreach message sent successfully.';
    header('Location: CandidateController.php?action=outreach_history'); exit;
}

// ── VIEW SEEKER PUBLIC PROFILE ────────────────────────────────
if ($action === 'view') {
    $seekerUserId = (int)($_GET['id'] ?? 0);
    $conn  = connect();
    $seeker = getSeekerPublicProfile($conn, $seekerUserId);
    $jobs   = getJobsByRecruiter($conn, $profileId);  // for outreach job dropdown
    close($conn);

    if (!$seeker) {
        $_SESSION['error'] = 'Candidate not found.';
        header('Location: CandidateController.php'); exit;
    }
    require_once '../view/recruiter/candidates.php';
    exit;
}

// ── OUTREACH FORM ─────────────────────────────────────────────
if ($action === 'outreach') {
    $seekerUserId = (int)($_GET['id'] ?? 0);
    $conn   = connect();
    $seeker = getSeekerPublicProfile($conn, $seekerUserId);
    $jobs   = getJobsByRecruiter($conn, $profileId);
    close($conn);

    if (!$seeker) {
        $_SESSION['error'] = 'Candidate not found.';
        header('Location: CandidateController.php'); exit;
    }
    require_once '../view/recruiter/candidates.php';
    exit;
}

// ── OUTREACH HISTORY ──────────────────────────────────────────
if ($action === 'outreach_history') {
    $conn     = connect();
    $outreach = getOutreachByRecruiter($conn, $profileId);
    close($conn);
    require_once '../view/recruiter/candidates.php';
    exit;
}

// ── DEFAULT: SEARCH PAGE ──────────────────────────────────────
$action = 'search';
$conn   = connect();
$jobs   = getJobsByRecruiter($conn, $profileId);  // for outreach dropdown
close($conn);
require_once '../view/recruiter/candidates.php';