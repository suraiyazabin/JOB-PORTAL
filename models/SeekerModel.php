<?php

// ── PROFILE ──
function getSeekerProfile($conn, $user_id) {
    $stmt = mysqli_prepare($conn, "SELECT u.*, sp.* FROM users u LEFT JOIN seeker_profiles sp ON u.id=sp.user_id WHERE u.id=?");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

function upsertSeekerProfile($conn, $user_id, $data) {
    // Update users table
    $stmt = mysqli_prepare($conn, "UPDATE users SET name=?, phone=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'ssi', $data['name'], $data['phone'], $user_id);
    mysqli_stmt_execute($stmt);

    // Check if seeker_profiles row exists
    $chk = mysqli_prepare($conn, "SELECT id FROM seeker_profiles WHERE user_id=?");
    mysqli_stmt_bind_param($chk, 'i', $user_id);
    mysqli_stmt_execute($chk);
    $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));

    if ($exists) {
        $stmt = mysqli_prepare($conn, "UPDATE seeker_profiles SET headline=?,summary=?,skills=?,years_experience=?,education_level=?,current_salary=?,expected_salary=?,preferred_location=? WHERE user_id=?");
        mysqli_stmt_bind_param($stmt,'sssisddssi',$data['headline'],$data['summary'],$data['skills'],$data['years_experience'],$data['education_level'],$data['current_salary'],$data['expected_salary'],$data['preferred_location'],$user_id);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO seeker_profiles (user_id,headline,summary,skills,years_experience,education_level,current_salary,expected_salary,preferred_location) VALUES (?,?,?,?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt,'isssisdds',$user_id,$data['headline'],$data['summary'],$data['skills'],$data['years_experience'],$data['education_level'],$data['current_salary'],$data['expected_salary'],$data['preferred_location']);
    }
    return mysqli_stmt_execute($stmt);
}

function updateResumePath($conn, $user_id, $path) {
    $chk = mysqli_prepare($conn, "SELECT id FROM seeker_profiles WHERE user_id=?");
    mysqli_stmt_bind_param($chk, 'i', $user_id);
    mysqli_stmt_execute($chk);
    $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));
    if ($exists) {
        $stmt = mysqli_prepare($conn, "UPDATE seeker_profiles SET resume_path=? WHERE user_id=?");
        mysqli_stmt_bind_param($stmt, 'si', $path, $user_id);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO seeker_profiles (user_id, resume_path) VALUES (?,?)");
        mysqli_stmt_bind_param($stmt, 'is', $user_id, $path);
    }
    return mysqli_stmt_execute($stmt);
}

function updateProfilePic($conn, $user_id, $path) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET profile_pic=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'si', $path, $user_id);
    return mysqli_stmt_execute($stmt);
}

function changePassword($conn, $user_id, $hash) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET password_hash=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'si', $hash, $user_id);
    return mysqli_stmt_execute($stmt);
}

// ── JOBS ──
function getActiveJobs($conn, $keyword='', $category='', $location='', $job_type='', $exp_level='', $salary_min='', $salary_max='') {
    $sql = "SELECT j.*, c.name cat_name, ep.company_name, ep.logo_path,
                   u.name poster_name
            FROM jobs j
            LEFT JOIN categories c ON j.category_id=c.id
            LEFT JOIN employer_profiles ep ON j.employer_id=ep.user_id
            LEFT JOIN users u ON j.employer_id=u.id
            WHERE j.status='active'";

    if ($keyword)   $sql .= " AND (j.title LIKE '%" . mysqli_real_escape_string($conn,$keyword) . "%' OR j.description LIKE '%" . mysqli_real_escape_string($conn,$keyword) . "%' OR ep.company_name LIKE '%" . mysqli_real_escape_string($conn,$keyword) . "%')";
    if ($category)  $sql .= " AND j.category_id=" . (int)$category;
    if ($location)  $sql .= " AND j.location LIKE '%" . mysqli_real_escape_string($conn,$location) . "%'";
    if ($job_type)  $sql .= " AND j.job_type='" . mysqli_real_escape_string($conn,$job_type) . "'";
    if ($exp_level) $sql .= " AND j.experience_level='" . mysqli_real_escape_string($conn,$exp_level) . "'";
    if ($salary_min) $sql .= " AND j.salary_max >= " . (float)$salary_min;
    if ($salary_max) $sql .= " AND j.salary_min <= " . (float)$salary_max;

    $sql .= " ORDER BY j.is_featured DESC, j.created_at DESC";
    $result = mysqli_query($conn, $sql);
    $rows = [];
    while ($r = mysqli_fetch_assoc($result)) $rows[] = $r;
    return $rows;
}

function getJobById($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT j.*, c.name cat_name, ep.company_name, ep.logo_path, ep.industry, ep.description company_desc, u.name poster_name FROM jobs j LEFT JOIN categories c ON j.category_id=c.id LEFT JOIN employer_profiles ep ON j.employer_id=ep.user_id LEFT JOIN users u ON j.employer_id=u.id WHERE j.id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// ── APPLICATIONS ──
function applyToJob($conn, $job_id, $seeker_id, $cover_letter, $resume_path) {
    // check duplicate
    $chk = mysqli_prepare($conn, "SELECT id FROM applications WHERE job_id=? AND seeker_id=?");
    mysqli_stmt_bind_param($chk, 'ii', $job_id, $seeker_id);
    mysqli_stmt_execute($chk);
    if (mysqli_fetch_assoc(mysqli_stmt_get_result($chk))) return 'duplicate';
    $stmt = mysqli_prepare($conn, "INSERT INTO applications (job_id, seeker_id, cover_letter, resume_path) VALUES (?,?,?,?)");
    mysqli_stmt_bind_param($stmt, 'iiss', $job_id, $seeker_id, $cover_letter, $resume_path);
    return mysqli_stmt_execute($stmt) ? true : false;
}

function getSeekerApplications($conn, $seeker_id) {
    $stmt = mysqli_prepare($conn, "SELECT a.*, j.title job_title, j.location, j.job_type, ep.company_name FROM applications a JOIN jobs j ON a.job_id=j.id LEFT JOIN employer_profiles ep ON j.employer_id=ep.user_id WHERE a.seeker_id=? ORDER BY a.applied_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $seeker_id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
    return $rows;
}

function withdrawApplication($conn, $app_id, $seeker_id) {
    $stmt = mysqli_prepare($conn, "UPDATE applications SET status='withdrawn' WHERE id=? AND seeker_id=? AND status='submitted'");
    mysqli_stmt_bind_param($stmt, 'ii', $app_id, $seeker_id);
    return mysqli_stmt_execute($stmt);
}

function hasApplied($conn, $job_id, $seeker_id) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM applications WHERE job_id=? AND seeker_id=? AND status != 'withdrawn'");
    mysqli_stmt_bind_param($stmt, 'ii', $job_id, $seeker_id);
    mysqli_stmt_execute($stmt);
    return (bool)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// ── SAVED JOBS ──
function toggleSaveJob($conn, $user_id, $job_id) {
    $chk = mysqli_prepare($conn, "SELECT id FROM saved_jobs WHERE user_id=? AND job_id=?");
    mysqli_stmt_bind_param($chk, 'ii', $user_id, $job_id);
    mysqli_stmt_execute($chk);
    if (mysqli_fetch_assoc(mysqli_stmt_get_result($chk))) {
        $d = mysqli_prepare($conn, "DELETE FROM saved_jobs WHERE user_id=? AND job_id=?");
        mysqli_stmt_bind_param($d, 'ii', $user_id, $job_id);
        mysqli_stmt_execute($d);
        return 'unsaved';
    } else {
        $s = mysqli_prepare($conn, "INSERT INTO saved_jobs (user_id, job_id) VALUES (?,?)");
        mysqli_stmt_bind_param($s, 'ii', $user_id, $job_id);
        mysqli_stmt_execute($s);
        return 'saved';
    }
}

function getSavedJobs($conn, $user_id) {
    $stmt = mysqli_prepare($conn, "SELECT j.*, c.name cat_name, ep.company_name, ep.logo_path, s.saved_at FROM saved_jobs s JOIN jobs j ON s.job_id=j.id LEFT JOIN categories c ON j.category_id=c.id LEFT JOIN employer_profiles ep ON j.employer_id=ep.user_id WHERE s.user_id=? ORDER BY s.saved_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
    return $rows;
}

function isSaved($conn, $user_id, $job_id) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM saved_jobs WHERE user_id=? AND job_id=?");
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $job_id);
    mysqli_stmt_execute($stmt);
    return (bool)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// ── ALERTS ──
function getSeekerAlerts($conn, $seeker_id) {
    $stmt = mysqli_prepare($conn, "SELECT a.*, c.name cat_name FROM job_alerts a LEFT JOIN categories c ON a.category_id=c.id WHERE a.seeker_id=? ORDER BY a.created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $seeker_id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
    return $rows;
}

function addAlert($conn, $seeker_id, $keyword, $cat_id, $location, $job_type) {
    $cat_id = $cat_id ?: null;
    $stmt = mysqli_prepare($conn, "INSERT INTO job_alerts (seeker_id, keyword, category_id, location, job_type) VALUES (?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt, 'isiss', $seeker_id, $keyword, $cat_id, $location, $job_type);
    return mysqli_stmt_execute($stmt);
}

function deleteAlert($conn, $id, $seeker_id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM job_alerts WHERE id=? AND seeker_id=?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $seeker_id);
    return mysqli_stmt_execute($stmt);
}

// ── MESSAGES ──
function getSeekerMessages($conn, $user_id) {
    $stmt = mysqli_prepare($conn, "SELECT m.*, u.name sender_name, u.role sender_role, u.profile_pic sender_pic FROM messages m JOIN users u ON m.sender_id=u.id WHERE m.recipient_id=? ORDER BY m.sent_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
    return $rows;
}

function sendMessage($conn, $sender_id, $recipient_id, $body, $app_id = null) {
    $stmt = mysqli_prepare($conn, "INSERT INTO messages (sender_id, recipient_id, body, application_id) VALUES (?,?,?,?)");
    mysqli_stmt_bind_param($stmt, 'iisi', $sender_id, $recipient_id, $body, $app_id);
    mysqli_stmt_execute($stmt);
    // mark original as read
    $upd = mysqli_prepare($conn, "UPDATE messages SET is_read=1 WHERE recipient_id=? AND sender_id=?");
    mysqli_stmt_bind_param($upd, 'ii', $sender_id, $recipient_id);
    mysqli_stmt_execute($upd);
    return true;
}

function markMessagesRead($conn, $user_id, $sender_id) {
    $stmt = mysqli_prepare($conn, "UPDATE messages SET is_read=1 WHERE recipient_id=? AND sender_id=?");
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $sender_id);
    return mysqli_stmt_execute($stmt);
}

// ── COMPLAINTS ──
function submitComplaint($conn, $submitter_id, $subject_id, $desc) {
    $stmt = mysqli_prepare($conn, "INSERT INTO complaints (submitter_id, subject_id, description) VALUES (?,?,?)");
    mysqli_stmt_bind_param($stmt, 'iis', $submitter_id, $subject_id, $desc);
    return mysqli_stmt_execute($stmt);
}

// ── SEEKER DASHBOARD ──
function getSeekerDashboard($conn, $seeker_id) {
    $data = [];
    $r = mysqli_query($conn, "SELECT COUNT(*) c FROM applications WHERE seeker_id=$seeker_id"); $data['total_apps'] = mysqli_fetch_assoc($r)['c'];
    $r = mysqli_query($conn, "SELECT COUNT(*) c FROM applications WHERE seeker_id=$seeker_id AND status='shortlisted'"); $data['shortlisted'] = mysqli_fetch_assoc($r)['c'];
    $r = mysqli_query($conn, "SELECT COUNT(*) c FROM saved_jobs WHERE user_id=$seeker_id"); $data['saved'] = mysqli_fetch_assoc($r)['c'];
    $r = mysqli_query($conn, "SELECT COUNT(*) c FROM job_alerts WHERE seeker_id=$seeker_id"); $data['alerts'] = mysqli_fetch_assoc($r)['c'];

    $stmt = mysqli_prepare($conn, "SELECT a.*, j.title job_title, ep.company_name FROM applications a JOIN jobs j ON a.job_id=j.id LEFT JOIN employer_profiles ep ON j.employer_id=ep.user_id WHERE a.seeker_id=? ORDER BY a.applied_at DESC LIMIT 5");
    mysqli_stmt_bind_param($stmt, 'i', $seeker_id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $data['recent_apps'] = [];
    while ($row = mysqli_fetch_assoc($r)) $data['recent_apps'][] = $row;

    $stmt2 = mysqli_prepare($conn, "SELECT j.*, ep.company_name, c.name cat_name FROM saved_jobs s JOIN jobs j ON s.job_id=j.id LEFT JOIN employer_profiles ep ON j.employer_id=ep.user_id LEFT JOIN categories c ON j.category_id=c.id WHERE s.user_id=? ORDER BY s.saved_at DESC LIMIT 4");
    mysqli_stmt_bind_param($stmt2, 'i', $seeker_id);
    mysqli_stmt_execute($stmt2);
    $r2 = mysqli_stmt_get_result($stmt2);
    $data['recent_saved'] = [];
    while ($row = mysqli_fetch_assoc($r2)) $data['recent_saved'][] = $row;

    return $data;
}