<?php

// ── DASHBOARD STATS ──
function getAdminStats($conn) {
    $stats = [];
    $res = mysqli_query($conn, "SELECT COUNT(*) c FROM users WHERE role='seeker' AND is_active=1"); $stats['seekers'] = mysqli_fetch_assoc($res)['c'];
    $res = mysqli_query($conn, "SELECT COUNT(*) c FROM users WHERE role='employer' AND is_active=1"); $stats['employers'] = mysqli_fetch_assoc($res)['c'];
    $res = mysqli_query($conn, "SELECT COUNT(*) c FROM users WHERE role='recruiter' AND is_active=1"); $stats['recruiters'] = mysqli_fetch_assoc($res)['c'];
    $res = mysqli_query($conn, "SELECT COUNT(*) c FROM jobs WHERE status='active'"); $stats['active_jobs'] = mysqli_fetch_assoc($res)['c'];
    $res = mysqli_query($conn, "SELECT COUNT(*) c FROM applications WHERE DATE(applied_at)=CURDATE()"); $stats['apps_today'] = mysqli_fetch_assoc($res)['c'];
    $res = mysqli_query($conn, "SELECT COUNT(*) c FROM users WHERE is_verified=0 AND role IN('employer','recruiter') AND is_active=1"); $stats['pending_verifications'] = mysqli_fetch_assoc($res)['c'];
    $res = mysqli_query($conn, "SELECT COUNT(*) c FROM complaints WHERE status='open'"); $stats['open_complaints'] = mysqli_fetch_assoc($res)['c'];
    $res = mysqli_query($conn, "SELECT COUNT(*) c FROM categories"); $stats['categories'] = mysqli_fetch_assoc($res)['c'];
    return $stats;
}

function getMonthlyRegistrations($conn) {
    $result = mysqli_query($conn, "SELECT DATE_FORMAT(created_at,'%b') mo, COUNT(*) c FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY created_at");
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    return $rows;
}

function getRecentActivity($conn) {
    $result = mysqli_query($conn, "SELECT u.name, u.role, u.created_at FROM users u WHERE u.role != 'admin' ORDER BY u.created_at DESC LIMIT 8");
    $rows = [];
    while ($r = mysqli_fetch_assoc($result)) $rows[] = $r;
    return $rows;
}

// ── EMPLOYERS ──
function getAllEmployers($conn, $search = '', $filter = '') {
    $s = '%' . mysqli_real_escape_string($conn, $search) . '%';
    $sql = "SELECT u.*, ep.company_name, ep.industry FROM users u LEFT JOIN employer_profiles ep ON u.id=ep.user_id WHERE u.role='employer'";
    if ($search) $sql .= " AND (u.name LIKE '$s' OR u.email LIKE '$s' OR ep.company_name LIKE '$s')";
    if ($filter === 'pending')  $sql .= " AND u.is_verified=0 AND u.is_active=1";
    if ($filter === 'verified') $sql .= " AND u.is_verified=1 AND u.is_active=1";
    if ($filter === 'suspended') $sql .= " AND u.is_active=0";
    $sql .= " ORDER BY u.created_at DESC";
    $result = mysqli_query($conn, $sql);
    $rows = [];
    while ($r = mysqli_fetch_assoc($result)) $rows[] = $r;
    return $rows;
}

function verifyUser($conn, $id) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET is_verified=1 WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    return mysqli_stmt_execute($stmt);
}

function rejectUser($conn, $id) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET is_active=0 WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    return mysqli_stmt_execute($stmt);
}

function toggleUserActive($conn, $id, $active) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET is_active=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'ii', $active, $id);
    return mysqli_stmt_execute($stmt);
}

// ── RECRUITERS ──
function getAllRecruiters($conn, $search = '', $filter = '') {
    $s = '%' . mysqli_real_escape_string($conn, $search) . '%';
    $sql = "SELECT u.*, rp.agency_name FROM users u LEFT JOIN recruiter_profiles rp ON u.id=rp.user_id WHERE u.role='recruiter'";
    if ($search) $sql .= " AND (u.name LIKE '$s' OR u.email LIKE '$s' OR rp.agency_name LIKE '$s')";
    if ($filter === 'pending')  $sql .= " AND u.is_verified=0 AND u.is_active=1";
    if ($filter === 'verified') $sql .= " AND u.is_verified=1 AND u.is_active=1";
    if ($filter === 'suspended') $sql .= " AND u.is_active=0";
    $sql .= " ORDER BY u.created_at DESC";
    $result = mysqli_query($conn, $sql);
    $rows = [];
    while ($r = mysqli_fetch_assoc($result)) $rows[] = $r;
    return $rows;
}

// ── SEEKERS ──
function getAllSeekers($conn, $search = '') {
    $s = '%' . mysqli_real_escape_string($conn, $search) . '%';
    $sql = "SELECT u.*, sp.headline FROM users u LEFT JOIN seeker_profiles sp ON u.id=sp.user_id WHERE u.role='seeker'";
    if ($search) $sql .= " AND (u.name LIKE '$s' OR u.email LIKE '$s')";
    $sql .= " ORDER BY u.created_at DESC";
    $result = mysqli_query($conn, $sql);
    $rows = [];
    while ($r = mysqli_fetch_assoc($result)) $rows[] = $r;
    return $rows;
}

// ── CATEGORIES ──
function getAllCategories($conn) {
    $result = mysqli_query($conn, "SELECT c.*, COUNT(j.id) job_count FROM categories c LEFT JOIN jobs j ON c.id=j.category_id AND j.status='active' GROUP BY c.id ORDER BY c.name");
    $rows = [];
    while ($r = mysqli_fetch_assoc($result)) $rows[] = $r;
    return $rows;
}

function addCategory($conn, $name, $desc) {
    $stmt = mysqli_prepare($conn, "INSERT INTO categories (name, description) VALUES (?,?)");
    mysqli_stmt_bind_param($stmt, 'ss', $name, $desc);
    return mysqli_stmt_execute($stmt);
}

function updateCategory($conn, $id, $name, $desc) {
    $stmt = mysqli_prepare($conn, "UPDATE categories SET name=?, description=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'ssi', $name, $desc, $id);
    return mysqli_stmt_execute($stmt);
}

function deleteCategory($conn, $id) {
    $check = mysqli_prepare($conn, "SELECT COUNT(*) c FROM jobs WHERE category_id=? AND status='active'");
    mysqli_stmt_bind_param($check, 'i', $id);
    mysqli_stmt_execute($check);
    $r = mysqli_stmt_get_result($check);
    if (mysqli_fetch_assoc($r)['c'] > 0) return false;
    $stmt = mysqli_prepare($conn, "DELETE FROM categories WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    return mysqli_stmt_execute($stmt);
}

// ── JOBS ──
function getAllJobsAdmin($conn, $search = '', $filter = '') {
    $s = '%' . mysqli_real_escape_string($conn, $search) . '%';
    $sql = "SELECT j.*, c.name cat_name, u.name poster_name, ep.company_name
            FROM jobs j
            LEFT JOIN categories c ON j.category_id=c.id
            LEFT JOIN users u ON j.employer_id=u.id OR j.recruiter_id=u.id
            LEFT JOIN employer_profiles ep ON j.employer_id=ep.user_id
            WHERE 1=1";
    if ($search) $sql .= " AND (j.title LIKE '$s' OR ep.company_name LIKE '$s' OR u.name LIKE '$s')";
    if ($filter === 'active')   $sql .= " AND j.status='active'";
    if ($filter === 'closed')   $sql .= " AND j.status='closed'";
    if ($filter === 'featured') $sql .= " AND j.is_featured=1";
    $sql .= " ORDER BY j.created_at DESC";
    $result = mysqli_query($conn, $sql);
    $rows = [];
    while ($r = mysqli_fetch_assoc($result)) $rows[] = $r;
    return $rows;
}

function toggleFeatured($conn, $id, $val) {
    $stmt = mysqli_prepare($conn, "UPDATE jobs SET is_featured=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'ii', $val, $id);
    return mysqli_stmt_execute($stmt);
}

function deleteJob($conn, $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM jobs WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    return mysqli_stmt_execute($stmt);
}

// ── COMPLAINTS ──
function getAllComplaints($conn, $filter = '') {
    $sql = "SELECT cp.*, u1.name submitter_name, u2.name subject_name, u2.role subject_role
            FROM complaints cp
            JOIN users u1 ON cp.submitter_id=u1.id
            JOIN users u2 ON cp.subject_id=u2.id WHERE 1=1";
    if ($filter === 'open')     $sql .= " AND cp.status='open'";
    if ($filter === 'resolved') $sql .= " AND cp.status='resolved'";
    $sql .= " ORDER BY cp.created_at DESC";
    $result = mysqli_query($conn, $sql);
    $rows = [];
    while ($r = mysqli_fetch_assoc($result)) $rows[] = $r;
    return $rows;
}

function resolveComplaint($conn, $id, $note) {
    $stmt = mysqli_prepare($conn, "UPDATE complaints SET status='resolved', admin_note=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'si', $note, $id);
    return mysqli_stmt_execute($stmt);
}

// ── ANALYTICS ──
function getAnalytics($conn) {
    $data = [];
    $r = mysqli_query($conn, "SELECT c.name, COUNT(j.id) cnt FROM categories c LEFT JOIN jobs j ON c.id=j.category_id AND j.status='active' GROUP BY c.id ORDER BY cnt DESC LIMIT 8");
    $data['jobs_per_cat'] = [];
    while ($row = mysqli_fetch_assoc($r)) $data['jobs_per_cat'][] = $row;

    $r = mysqli_query($conn, "SELECT DATE_FORMAT(applied_at,'%b') mo, COUNT(*) cnt FROM applications WHERE applied_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(applied_at,'%Y-%m') ORDER BY applied_at");
    $data['apps_monthly'] = [];
    while ($row = mysqli_fetch_assoc($r)) $data['apps_monthly'][] = $row;

    $r = mysqli_query($conn, "SELECT ep.company_name, COUNT(a.id) cnt FROM applications a JOIN jobs j ON a.job_id=j.id JOIN employer_profiles ep ON j.employer_id=ep.user_id GROUP BY j.employer_id ORDER BY cnt DESC LIMIT 5");
    $data['top_employers'] = [];
    while ($row = mysqli_fetch_assoc($r)) $data['top_employers'][] = $row;

    return $data;
}

// ── ANNOUNCEMENTS ──
function getAllAnnouncements($conn) {
    $result = mysqli_query($conn, "SELECT a.*, u.name admin_name FROM announcements a JOIN users u ON a.admin_id=u.id ORDER BY a.created_at DESC");
    $rows = [];
    while ($r = mysqli_fetch_assoc($result)) $rows[] = $r;
    return $rows;
}

function addAnnouncement($conn, $admin_id, $title, $body) {
    $stmt = mysqli_prepare($conn, "INSERT INTO announcements (admin_id, title, body) VALUES (?,?,?)");
    mysqli_stmt_bind_param($stmt, 'iss', $admin_id, $title, $body);
    return mysqli_stmt_execute($stmt);
}

function deleteAnnouncement($conn, $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM announcements WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    return mysqli_stmt_execute($stmt);
}