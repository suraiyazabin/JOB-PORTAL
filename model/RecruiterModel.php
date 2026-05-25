<?php

// ============================================================
//  RECRUITER PROFILE
// ============================================================

function insertRecruiterProfile($conn, $userId, $agencyName, $specialization, $description, $website)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO recruiter_profiles (user_id, agency_name, specialization, description, website)
         VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issss", $userId, $agencyName, $specialization, $description, $website);
    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $id;
}

function getRecruiterProfile($conn, $profileId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT rp.*, u.name, u.email, u.phone, u.profile_pic, u.password_hash
         FROM recruiter_profiles rp
         JOIN users u ON rp.user_id = u.id
         WHERE rp.id = ?");
    mysqli_stmt_bind_param($stmt, "i", $profileId);
    mysqli_stmt_execute($stmt);
    $result  = mysqli_stmt_get_result($stmt);
    $profile = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $profile;
}

function updateRecruiterProfile($conn, $profileId, $agencyName, $specialization, $description, $website, $name, $phone, $userId)
{
    $stmt = mysqli_prepare($conn,
        "UPDATE recruiter_profiles
         SET agency_name=?, specialization=?, description=?, website=?
         WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssssi", $agencyName, $specialization, $description, $website, $profileId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt2 = mysqli_prepare($conn, "UPDATE users SET name=?, phone=? WHERE id=?");
    mysqli_stmt_bind_param($stmt2, "ssi", $name, $phone, $userId);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);
}

// ============================================================
//  CLIENT MANAGEMENT
// ============================================================

function getClientsByRecruiter($conn, $recruiterId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT rc.*, ep.company_name AS registered_company_name, ep.industry, ep.logo_path,
                (SELECT COUNT(*) FROM jobs j WHERE j.recruiter_id = rc.recruiter_id AND j.employer_id = ep.id) AS job_count
         FROM recruiter_clients rc
         LEFT JOIN employer_profiles ep ON rc.employer_id = ep.id
         WHERE rc.recruiter_id = ?
         ORDER BY rc.added_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $recruiterId);
    mysqli_stmt_execute($stmt);
    $result  = mysqli_stmt_get_result($stmt);
    $clients = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $clients[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $clients;
}

function getClientById($conn, $clientId, $recruiterId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT rc.*, ep.company_name AS registered_company_name, ep.industry
         FROM recruiter_clients rc
         LEFT JOIN employer_profiles ep ON rc.employer_id = ep.id
         WHERE rc.id = ? AND rc.recruiter_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $clientId, $recruiterId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $client = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $client;
}

function addClient($conn, $recruiterId, $employerId, $companyNameOverride)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO recruiter_clients (recruiter_id, employer_id, company_name_override)
         VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iis", $recruiterId, $employerId, $companyNameOverride);
    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $id;
}

function deleteClient($conn, $clientId, $recruiterId)
{
    $stmt = mysqli_prepare($conn, "DELETE FROM recruiter_clients WHERE id=? AND recruiter_id=?");
    mysqli_stmt_bind_param($stmt, "ii", $clientId, $recruiterId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Get verified employers (for recruiter to link a client)
function getAllVerifiedEmployers($conn)
{
    $result = mysqli_query($conn,
        "SELECT ep.id, ep.company_name, ep.industry
         FROM employer_profiles ep
         JOIN users u ON ep.user_id = u.id
         WHERE u.is_verified = 1 AND u.is_active = 1
         ORDER BY ep.company_name");
    $list = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

function getClientDisplayName($client)
{
    return $client['company_name_override'] ?: ($client['registered_company_name'] ?? 'Unknown Company');
}

// ============================================================
//  RECRUITER JOB POSTING
// ============================================================

function getJobsByRecruiter($conn, $recruiterId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT j.*, c.name AS category_name,
                COALESCE(rc.company_name_override, ep.company_name) AS client_name,
                (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS app_count
         FROM jobs j
         LEFT JOIN categories c ON j.category_id = c.id
         LEFT JOIN employer_profiles ep ON j.employer_id = ep.id
         LEFT JOIN recruiter_clients rc ON rc.recruiter_id = j.recruiter_id AND rc.employer_id = j.employer_id
         WHERE j.recruiter_id = ?
         ORDER BY j.created_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $recruiterId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $jobs   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $jobs;
}

function getRecruiterJobById($conn, $jobId, $recruiterId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT j.*, c.name AS category_name
         FROM jobs j
         LEFT JOIN categories c ON j.category_id = c.id
         WHERE j.id = ? AND j.recruiter_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $jobId, $recruiterId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $job    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $job;
}

function createRecruiterJob($conn, $recruiterId, $employerId, $categoryId, $title, $description, $requirements, $benefits, $salaryMin, $salaryMax, $location, $jobType, $experienceLevel, $deadline, $status)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO jobs (recruiter_id, employer_id, category_id, title, description, requirements, benefits,
                           salary_min, salary_max, location, job_type, experience_level, deadline, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiissssddsssss",
        $recruiterId, $employerId, $categoryId, $title, $description, $requirements, $benefits,
        $salaryMin, $salaryMax, $location, $jobType, $experienceLevel, $deadline, $status);
    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $id;
}

function updateRecruiterJob($conn, $jobId, $recruiterId, $categoryId, $title, $description, $requirements, $benefits, $salaryMin, $salaryMax, $location, $jobType, $experienceLevel, $deadline, $status)
{
    $stmt = mysqli_prepare($conn,
        "UPDATE jobs
         SET category_id=?, title=?, description=?, requirements=?, benefits=?,
             salary_min=?, salary_max=?, location=?, job_type=?, experience_level=?, deadline=?, status=?
         WHERE id=? AND recruiter_id=?");
    mysqli_stmt_bind_param($stmt, "issssddsssssii",
        $categoryId, $title, $description, $requirements, $benefits,
        $salaryMin, $salaryMax, $location, $jobType, $experienceLevel, $deadline, $status,
        $jobId, $recruiterId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function deleteRecruiterJob($conn, $jobId, $recruiterId)
{
    $stmt = mysqli_prepare($conn, "DELETE FROM jobs WHERE id=? AND recruiter_id=?");
    mysqli_stmt_bind_param($stmt, "ii", $jobId, $recruiterId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ============================================================
//  CANDIDATE SEARCH (AJAX-powered)
// ============================================================

function searchSeekers($conn, $keyword, $location, $experienceLevel, $educationLevel)
{
    // Build dynamic WHERE clauses
    $conditions = ["u.role = 'seeker'", "u.is_active = 1"];
    $params     = [];
    $types      = "";

    if ($keyword) {
        $kw = '%' . $keyword . '%';
        $conditions[] = "(u.name LIKE ? OR sp.headline LIKE ? OR sp.skills LIKE ? OR sp.summary LIKE ?)";
        $params = array_merge($params, [$kw, $kw, $kw, $kw]);
        $types .= "ssss";
    }
    if ($location) {
        $loc = '%' . $location . '%';
        $conditions[] = "sp.preferred_location LIKE ?";
        $params[]  = $loc;
        $types    .= "s";
    }
    if ($experienceLevel === 'entry') {
        $conditions[] = "sp.years_experience <= 2";
    } elseif ($experienceLevel === 'mid') {
        $conditions[] = "sp.years_experience BETWEEN 3 AND 6";
    } elseif ($experienceLevel === 'senior') {
        $conditions[] = "sp.years_experience > 6";
    }
    if ($educationLevel) {
        $conditions[] = "sp.education_level = ?";
        $params[]  = $educationLevel;
        $types    .= "s";
    }

    $where = implode(" AND ", $conditions);
    $sql   = "SELECT u.id AS user_id, u.name, u.email, u.profile_pic,
                     sp.headline, sp.skills, sp.years_experience, sp.education_level,
                     sp.expected_salary, sp.preferred_location
              FROM users u
              LEFT JOIN seeker_profiles sp ON sp.user_id = u.id
              WHERE $where
              ORDER BY u.name
              LIMIT 50";

    $stmt = mysqli_prepare($conn, $sql);
    if ($types && $params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result  = mysqli_stmt_get_result($stmt);
    $seekers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $seekers[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $seekers;
}

function getSeekerPublicProfile($conn, $seekerUserId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT u.id, u.name, u.email, u.phone, u.profile_pic,
                sp.headline, sp.summary, sp.skills, sp.years_experience,
                sp.education_level, sp.expected_salary, sp.preferred_location, sp.resume_path
         FROM users u
         LEFT JOIN seeker_profiles sp ON sp.user_id = u.id
         WHERE u.id = ? AND u.role = 'seeker'");
    mysqli_stmt_bind_param($stmt, "i", $seekerUserId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $seeker = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $seeker;
}

// ============================================================
//  OUTREACH
// ============================================================

function sendOutreach($conn, $recruiterId, $seekerId, $jobId, $message)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO recruiter_outreach (recruiter_id, seeker_id, job_id, message)
         VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiis", $recruiterId, $seekerId, $jobId, $message);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function getOutreachByRecruiter($conn, $recruiterId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT ro.*, u.name AS seeker_name, u.email AS seeker_email,
                j.title AS job_title
         FROM recruiter_outreach ro
         JOIN users u ON ro.seeker_id = u.id
         LEFT JOIN jobs j ON ro.job_id = j.id
         WHERE ro.recruiter_id = ?
         ORDER BY ro.sent_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $recruiterId);
    mysqli_stmt_execute($stmt);
    $result  = mysqli_stmt_get_result($stmt);
    $outreach = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $outreach[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $outreach;
}

// ============================================================
//  PIPELINE (Applications across all recruiter jobs)
// ============================================================

function getPipelineByRecruiter($conn, $recruiterId, $filterStatus = '')
{
    $sql = "SELECT a.*, u.name AS seeker_name, u.email AS seeker_email,
                   sp.headline, sp.skills, sp.years_experience,
                   j.title AS job_title,
                   COALESCE(rc.company_name_override, ep.company_name) AS client_name
            FROM applications a
            JOIN users u ON a.seeker_id = u.id
            LEFT JOIN seeker_profiles sp ON sp.user_id = u.id
            JOIN jobs j ON a.job_id = j.id
            LEFT JOIN employer_profiles ep ON j.employer_id = ep.id
            LEFT JOIN recruiter_clients rc ON rc.recruiter_id = j.recruiter_id AND rc.employer_id = j.employer_id
            WHERE j.recruiter_id = ?";

    if ($filterStatus) {
        $sql .= " AND a.status = ?";
    }
    $sql .= " ORDER BY j.title, a.applied_at DESC";

    $stmt = mysqli_prepare($conn, $sql);
    if ($filterStatus) {
        mysqli_stmt_bind_param($stmt, "is", $recruiterId, $filterStatus);
    } else {
        mysqli_stmt_bind_param($stmt, "i", $recruiterId);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $apps   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $apps[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $apps;
}

function updatePipelineStatus($conn, $appId, $status, $recruiterId)
{
    $stmt = mysqli_prepare($conn,
        "UPDATE applications a
         JOIN jobs j ON a.job_id = j.id
         SET a.status = ?
         WHERE a.id = ? AND j.recruiter_id = ?");
    mysqli_stmt_bind_param($stmt, "sii", $status, $appId, $recruiterId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ============================================================
//  RECRUITER ANALYTICS
// ============================================================

function getRecruiterAnalytics($conn, $recruiterId)
{
    $data = [];

    // Total outreach sent
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM recruiter_outreach WHERE recruiter_id=?");
    mysqli_stmt_bind_param($stmt, "i", $recruiterId);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $data['total_outreach'] = mysqli_fetch_assoc($r)['total'] ?? 0;
    mysqli_stmt_close($stmt);

    // Responded outreach
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM recruiter_outreach WHERE recruiter_id=? AND status='responded'");
    mysqli_stmt_bind_param($stmt, "i", $recruiterId);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $responded = mysqli_fetch_assoc($r)['total'] ?? 0;
    $data['responded_outreach'] = $responded;
    $data['response_rate'] = $data['total_outreach'] > 0
        ? round(($responded / $data['total_outreach']) * 100, 1)
        : 0;
    mysqli_stmt_close($stmt);

    // Total applications managed
    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS total FROM applications a
         JOIN jobs j ON a.job_id = j.id
         WHERE j.recruiter_id=?");
    mysqli_stmt_bind_param($stmt, "i", $recruiterId);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $data['total_applications'] = mysqli_fetch_assoc($r)['total'] ?? 0;
    mysqli_stmt_close($stmt);

    // Total clients
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM recruiter_clients WHERE recruiter_id=?");
    mysqli_stmt_bind_param($stmt, "i", $recruiterId);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $data['total_clients'] = mysqli_fetch_assoc($r)['total'] ?? 0;
    mysqli_stmt_close($stmt);

    // Total jobs posted
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM jobs WHERE recruiter_id=?");
    mysqli_stmt_bind_param($stmt, "i", $recruiterId);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $data['total_jobs'] = mysqli_fetch_assoc($r)['total'] ?? 0;
    mysqli_stmt_close($stmt);

    // Pipeline breakdown
    $stmt = mysqli_prepare($conn,
        "SELECT a.status, COUNT(*) AS cnt
         FROM applications a JOIN jobs j ON a.job_id = j.id
         WHERE j.recruiter_id=?
         GROUP BY a.status");
    mysqli_stmt_bind_param($stmt, "i", $recruiterId);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $data['pipeline'] = [];
    while ($row = mysqli_fetch_assoc($r)) {
        $data['pipeline'][$row['status']] = $row['cnt'];
    }
    mysqli_stmt_close($stmt);

    return $data;
}

function getClientReport($conn, $recruiterId, $clientId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT j.id, j.title, j.status,
                COUNT(a.id) AS total_apps,
                SUM(CASE WHEN a.status='submitted'   THEN 1 ELSE 0 END) AS submitted,
                SUM(CASE WHEN a.status='reviewed'    THEN 1 ELSE 0 END) AS reviewed,
                SUM(CASE WHEN a.status='shortlisted' THEN 1 ELSE 0 END) AS shortlisted,
                SUM(CASE WHEN a.status='interview'   THEN 1 ELSE 0 END) AS interview,
                SUM(CASE WHEN a.status='rejected'    THEN 1 ELSE 0 END) AS rejected
         FROM jobs j
         LEFT JOIN applications a ON a.job_id = j.id
         JOIN recruiter_clients rc ON rc.recruiter_id = j.recruiter_id
             AND (rc.employer_id = j.employer_id OR j.employer_id IS NULL)
         WHERE j.recruiter_id = ? AND rc.id = ?
         GROUP BY j.id, j.title, j.status
         ORDER BY j.created_at DESC");
    mysqli_stmt_bind_param($stmt, "ii", $recruiterId, $clientId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}