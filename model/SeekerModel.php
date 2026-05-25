<?php

// ============================================================
//  SEEKER PROFILE
// ============================================================

function getSeekerProfileByUserId($conn, $userId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT sp.*, u.name, u.email, u.phone, u.profile_pic
         FROM users u
         LEFT JOIN seeker_profiles sp ON sp.user_id = u.id
         WHERE u.id = ?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result  = mysqli_stmt_get_result($stmt);
    $profile = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $profile;
}

function upsertSeekerProfile($conn, $userId, $headline, $summary, $skills, $yearsExp,
                              $educationLevel, $currentSalary, $expectedSalary,
                              $preferredLocation, $resumePath)
{
    // Check if profile exists
    $stmt = mysqli_prepare($conn, "SELECT id FROM seeker_profiles WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if ($exists) {
        $stmt = mysqli_prepare($conn,
            "UPDATE seeker_profiles
             SET headline=?, summary=?, skills=?, years_experience=?, education_level=?,
                 current_salary=?, expected_salary=?, preferred_location=?, resume_path=?
             WHERE user_id=?");
        mysqli_stmt_bind_param($stmt, "sssisddssi",
            $headline, $summary, $skills, $yearsExp, $educationLevel,
            $currentSalary, $expectedSalary, $preferredLocation, $resumePath, $userId);
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO seeker_profiles
             (user_id, headline, summary, skills, years_experience, education_level,
              current_salary, expected_salary, preferred_location, resume_path)
             VALUES (?,?,?,?,?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "isssisddss",
            $userId, $headline, $summary, $skills, $yearsExp, $educationLevel,
            $currentSalary, $expectedSalary, $preferredLocation, $resumePath);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function updateSeekerUser($conn, $userId, $name, $phone, $profilePic)
{
    if ($profilePic) {
        $stmt = mysqli_prepare($conn,
            "UPDATE users SET name=?, phone=?, profile_pic=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssi", $name, $phone, $profilePic, $userId);
    } else {
        $stmt = mysqli_prepare($conn,
            "UPDATE users SET name=?, phone=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssi", $name, $phone, $userId);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ============================================================
//  JOB BROWSING
// ============================================================

function getActiveJobs($conn, $keyword = '', $categoryId = 0,
                        $location = '', $jobType = '', $expLevel = '',
                        $salaryMin = 0, $salaryMax = 0)
{
    $conds  = ["j.status = 'active'", "(j.deadline IS NULL OR j.deadline >= CURDATE())"];
    $params = [];
    $types  = '';

    if ($keyword) {
        $kw = '%' . $keyword . '%';
        $conds[]  = "(j.title LIKE ? OR j.description LIKE ? OR ep.company_name LIKE ? OR rp.agency_name LIKE ?)";
        $params   = array_merge($params, [$kw, $kw, $kw, $kw]);
        $types   .= "ssss";
    }
    if ($categoryId) {
        $conds[]  = "j.category_id = ?";
        $params[] = $categoryId;
        $types   .= "i";
    }
    if ($location) {
        $loc = '%' . $location . '%';
        $conds[]  = "j.location LIKE ?";
        $params[] = $loc;
        $types   .= "s";
    }
    if ($jobType) {
        $conds[]  = "j.job_type = ?";
        $params[] = $jobType;
        $types   .= "s";
    }
    if ($expLevel) {
        $conds[]  = "j.experience_level = ?";
        $params[] = $expLevel;
        $types   .= "s";
    }
    if ($salaryMin > 0) {
        $conds[]  = "j.salary_max >= ?";
        $params[] = $salaryMin;
        $types   .= "d";
    }
    if ($salaryMax > 0) {
        $conds[]  = "j.salary_min <= ?";
        $params[] = $salaryMax;
        $types   .= "d";
    }

    $where = 'WHERE ' . implode(' AND ', $conds);
    $sql   = "SELECT j.*, c.name AS category_name,
                     COALESCE(ep.company_name, rp.agency_name) AS company_name,
                     COALESCE(ep.logo_path, '') AS company_logo,
                     COALESCE(ep.industry, rp.specialization, '') AS industry
              FROM jobs j
              LEFT JOIN categories c ON j.category_id = c.id
              LEFT JOIN employer_profiles  ep ON j.employer_id  = ep.id
              LEFT JOIN recruiter_profiles rp ON j.recruiter_id = rp.id
              $where
              ORDER BY j.is_featured DESC, j.created_at DESC
              LIMIT 100";

    $stmt = mysqli_prepare($conn, $sql);
    if ($types && $params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $jobs   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $jobs;
}

function getJobDetail($conn, $jobId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT j.*, c.name AS category_name,
                COALESCE(ep.company_name, rp.agency_name) AS company_name,
                COALESCE(ep.logo_path, '') AS company_logo,
                ep.description AS company_desc, ep.website AS company_website,
                ep.address AS company_address, ep.industry,
                rp.agency_name, rp.specialization
         FROM jobs j
         LEFT JOIN categories c ON j.category_id = c.id
         LEFT JOIN employer_profiles  ep ON j.employer_id  = ep.id
         LEFT JOIN recruiter_profiles rp ON j.recruiter_id = rp.id
         WHERE j.id = ? AND j.status = 'active'");
    mysqli_stmt_bind_param($stmt, "i", $jobId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $job    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $job;
}

// ============================================================
//  APPLICATIONS
// ============================================================

function hasApplied($conn, $jobId, $seekerUserId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT id FROM applications WHERE job_id=? AND seeker_id=?");
    mysqli_stmt_bind_param($stmt, "ii", $jobId, $seekerUserId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $has = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $has;
}

function applyToJob($conn, $jobId, $seekerUserId, $coverLetter, $resumePath)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO applications (job_id, seeker_id, cover_letter, resume_path)
         VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiss", $jobId, $seekerUserId, $coverLetter, $resumePath);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function getSeekerApplications($conn, $seekerUserId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT a.*, j.title AS job_title, j.location, j.job_type, j.deadline,
                COALESCE(ep.company_name, rp.agency_name) AS company_name,
                COALESCE(ep.logo_path, '') AS company_logo
         FROM applications a
         JOIN jobs j ON a.job_id = j.id
         LEFT JOIN employer_profiles  ep ON j.employer_id  = ep.id
         LEFT JOIN recruiter_profiles rp ON j.recruiter_id = rp.id
         WHERE a.seeker_id = ?
         ORDER BY a.applied_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $seekerUserId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $apps   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $apps[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $apps;
}

function withdrawApplication($conn, $appId, $seekerUserId)
{
    // Can only withdraw if status is 'submitted'
    $stmt = mysqli_prepare($conn,
        "UPDATE applications SET status='withdrawn'
         WHERE id=? AND seeker_id=? AND status='submitted'");
    mysqli_stmt_bind_param($stmt, "ii", $appId, $seekerUserId);
    mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $affected > 0;
}

// ============================================================
//  SAVED JOBS
// ============================================================

function saveJob($conn, $userId, $jobId)
{
    $stmt = mysqli_prepare($conn,
        "INSERT IGNORE INTO saved_jobs (user_id, job_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ii", $userId, $jobId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function unsaveJob($conn, $userId, $jobId)
{
    $stmt = mysqli_prepare($conn,
        "DELETE FROM saved_jobs WHERE user_id=? AND job_id=?");
    mysqli_stmt_bind_param($stmt, "ii", $userId, $jobId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function getSavedJobs($conn, $userId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT j.*, sj.saved_at, c.name AS category_name,
                COALESCE(ep.company_name, rp.agency_name) AS company_name
         FROM saved_jobs sj
         JOIN jobs j ON sj.job_id = j.id
         LEFT JOIN categories c ON j.category_id = c.id
         LEFT JOIN employer_profiles  ep ON j.employer_id  = ep.id
         LEFT JOIN recruiter_profiles rp ON j.recruiter_id = rp.id
         WHERE sj.user_id = ?
         ORDER BY sj.saved_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $jobs   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $jobs;
}

function isSaved($conn, $userId, $jobId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT id FROM saved_jobs WHERE user_id=? AND job_id=?");
    mysqli_stmt_bind_param($stmt, "ii", $userId, $jobId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $saved = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $saved;
}

// ============================================================
//  SEEKER DASHBOARD STATS
// ============================================================

function getSeekerStats($conn, $userId)
{
    $stats = [];

    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS cnt FROM applications WHERE seeker_id=?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $stats['total_apps'] = mysqli_fetch_assoc($r)['cnt'] ?? 0;
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS cnt FROM applications WHERE seeker_id=? AND status='shortlisted'");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $stats['shortlisted'] = mysqli_fetch_assoc($r)['cnt'] ?? 0;
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS cnt FROM applications WHERE seeker_id=? AND status='interview'");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $stats['interview'] = mysqli_fetch_assoc($r)['cnt'] ?? 0;
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS cnt FROM saved_jobs WHERE user_id=?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $stats['saved'] = mysqli_fetch_assoc($r)['cnt'] ?? 0;
    mysqli_stmt_close($stmt);

    return $stats;
}