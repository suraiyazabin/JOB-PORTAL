<?php

// ============================================================
//  ADMIN — DASHBOARD STATS
// ============================================================

function getAdminDashboardStats($conn)
{
    $stats = [];

    // Users by role
    $result = mysqli_query($conn,
        "SELECT role, COUNT(*) AS cnt FROM users GROUP BY role");
    $stats['users'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $stats['users'][$row['role']] = $row['cnt'];
    }

    // Pending verifications
    $result = mysqli_query($conn,
        "SELECT COUNT(*) AS cnt FROM users
         WHERE is_verified = 0 AND role IN ('employer','recruiter')");
    $stats['pending'] = mysqli_fetch_assoc($result)['cnt'] ?? 0;

    // Total active jobs
    $result = mysqli_query($conn,
        "SELECT COUNT(*) AS cnt FROM jobs WHERE status = 'active'");
    $stats['active_jobs'] = mysqli_fetch_assoc($result)['cnt'] ?? 0;

    // Applications today
    $result = mysqli_query($conn,
        "SELECT COUNT(*) AS cnt FROM applications WHERE DATE(applied_at) = CURDATE()");
    $stats['apps_today'] = mysqli_fetch_assoc($result)['cnt'] ?? 0;

    // Open complaints
    $result = mysqli_query($conn,
        "SELECT COUNT(*) AS cnt FROM complaints WHERE status = 'open'");
    $stats['open_complaints'] = mysqli_fetch_assoc($result)['cnt'] ?? 0;

    return $stats;
}

// ============================================================
//  ADMIN — USER MANAGEMENT
// ============================================================

function getPendingUsers($conn)
{
    $result = mysqli_query($conn,
        "SELECT u.id, u.name, u.email, u.phone, u.role, u.created_at,
                COALESCE(ep.company_name, rp.agency_name) AS org_name
         FROM users u
         LEFT JOIN employer_profiles  ep ON ep.user_id = u.id
         LEFT JOIN recruiter_profiles rp ON rp.user_id = u.id
         WHERE u.is_verified = 0 AND u.role IN ('employer','recruiter')
         ORDER BY u.created_at ASC");
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function getAllUsersByRole($conn, $role)
{
    $stmt = mysqli_prepare($conn,
        "SELECT u.id, u.name, u.email, u.phone, u.is_active, u.is_verified, u.created_at,
                COALESCE(ep.company_name, rp.agency_name) AS org_name
         FROM users u
         LEFT JOIN employer_profiles  ep ON ep.user_id = u.id
         LEFT JOIN recruiter_profiles rp ON rp.user_id = u.id
         WHERE u.role = ?
         ORDER BY u.created_at DESC");
    mysqli_stmt_bind_param($stmt, "s", $role);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function getAllSeekers($conn, $search = '')
{
    if ($search) {
        $like = '%' . $search . '%';
        $stmt = mysqli_prepare($conn,
            "SELECT id, name, email, phone, is_active, is_verified, created_at
             FROM users WHERE role='seeker' AND (name LIKE ? OR email LIKE ?)
             ORDER BY created_at DESC");
        mysqli_stmt_bind_param($stmt, "ss", $like, $like);
    } else {
        $stmt = mysqli_prepare($conn,
            "SELECT id, name, email, phone, is_active, is_verified, created_at
             FROM users WHERE role='seeker' ORDER BY created_at DESC");
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function approveUser($conn, $userId)
{
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET is_verified = 1 WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function rejectUser($conn, $userId)
{
    // Soft reject: mark not verified, not active
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET is_verified = 0, is_active = 0 WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function setUserActive($conn, $userId, $status)
{
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET is_active = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $status, $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ============================================================
//  ADMIN — JOB MANAGEMENT
// ============================================================

function getAllJobsAdmin($conn, $search = '', $status = '')
{
    $conds  = [];
    $params = [];
    $types  = '';

    if ($search) {
        $like = '%' . $search . '%';
        $conds[]  = "(j.title LIKE ? OR ep.company_name LIKE ? OR rp.agency_name LIKE ?)";
        $params   = array_merge($params, [$like, $like, $like]);
        $types   .= "sss";
    }
    if ($status) {
        $conds[]  = "j.status = ?";
        $params[] = $status;
        $types   .= "s";
    }

    $where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';
    $sql = "SELECT j.id, j.title, j.status, j.location, j.created_at, j.deadline,
                   c.name AS category_name,
                   COALESCE(ep.company_name, rp.agency_name) AS posted_by
            FROM jobs j
            LEFT JOIN categories c ON j.category_id = c.id
            LEFT JOIN employer_profiles  ep ON j.employer_id  = ep.id
            LEFT JOIN recruiter_profiles rp ON j.recruiter_id = rp.id
            $where
            ORDER BY j.created_at DESC
            LIMIT 200";

    $stmt = mysqli_prepare($conn, $sql);
    if ($types && $params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function removeJob($conn, $jobId)
{
    $stmt = mysqli_prepare($conn, "DELETE FROM jobs WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $jobId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function setJobFeatured($conn, $jobId, $isFeatured)
{
    $stmt = mysqli_prepare($conn, "UPDATE jobs SET is_featured = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $isFeatured, $jobId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ============================================================
//  ADMIN — CATEGORIES
// ============================================================

function getCategoryWithJobCount($conn)
{
    $result = mysqli_query($conn,
        "SELECT c.id, c.name, c.description,
                COUNT(j.id) AS job_count
         FROM categories c
         LEFT JOIN jobs j ON j.category_id = c.id
         GROUP BY c.id, c.name, c.description
         ORDER BY c.name");
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function getCategoryById($conn, $id)
{
    $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function addCategory($conn, $name, $description)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO categories (name, description) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ss", $name, $description);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function updateCategory($conn, $id, $name, $description)
{
    $stmt = mysqli_prepare($conn,
        "UPDATE categories SET name = ?, description = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssi", $name, $description, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function deleteCategory($conn, $id)
{
    // Only delete if no active jobs reference it
    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS cnt FROM jobs WHERE category_id = ? AND status = 'active'");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $cnt    = mysqli_fetch_assoc($result)['cnt'] ?? 0;
    mysqli_stmt_close($stmt);

    if ($cnt > 0) {
        return false; // blocked
    }
    $stmt = mysqli_prepare($conn, "DELETE FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return true;
}

// ============================================================
//  ADMIN — COMPLAINTS
// ============================================================

function getAllComplaints($conn)
{
    $result = mysqli_query($conn,
        "SELECT c.*, 
                u1.name AS submitter_name, u1.email AS submitter_email,
                u2.name AS subject_name,   u2.email AS subject_email
         FROM complaints c
         JOIN users u1 ON c.submitter_id = u1.id
         JOIN users u2 ON c.subject_id   = u2.id
         ORDER BY c.created_at DESC");
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function resolveComplaint($conn, $complaintId, $adminNote)
{
    $stmt = mysqli_prepare($conn,
        "UPDATE complaints SET status = 'resolved', admin_note = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $adminNote, $complaintId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ============================================================
//  ADMIN — PLATFORM REPORTS
// ============================================================

function getJobsByCategory($conn)
{
    $result = mysqli_query($conn,
        "SELECT c.name, COUNT(j.id) AS total,
                SUM(CASE WHEN j.status='active' THEN 1 ELSE 0 END) AS active
         FROM categories c
         LEFT JOIN jobs j ON j.category_id = c.id
         GROUP BY c.id, c.name
         ORDER BY total DESC");
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function getNewRegistrationsPerMonth($conn)
{
    $result = mysqli_query($conn,
        "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
                role, COUNT(*) AS cnt
         FROM users
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
         GROUP BY month, role
         ORDER BY month ASC");
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function getTopEmployers($conn)
{
    $result = mysqli_query($conn,
        "SELECT ep.company_name, COUNT(a.id) AS app_count,
                COUNT(DISTINCT j.id) AS job_count
         FROM employer_profiles ep
         JOIN jobs j ON j.employer_id = ep.id
         JOIN applications a ON a.job_id = j.id
         GROUP BY ep.id, ep.company_name
         ORDER BY app_count DESC
         LIMIT 10");
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function getMostActiveRecruiters($conn)
{
    $result = mysqli_query($conn,
        "SELECT rp.agency_name, COUNT(j.id) AS jobs_posted,
                COUNT(ro.id) AS outreach_sent
         FROM recruiter_profiles rp
         LEFT JOIN jobs j ON j.recruiter_id = rp.id
         LEFT JOIN recruiter_outreach ro ON ro.recruiter_id = rp.id
         GROUP BY rp.id, rp.agency_name
         ORDER BY jobs_posted DESC
         LIMIT 10");
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function getPopularLocations($conn)
{
    $result = mysqli_query($conn,
        "SELECT location, COUNT(*) AS cnt
         FROM jobs WHERE status = 'active' AND location != ''
         GROUP BY location ORDER BY cnt DESC LIMIT 10");
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}