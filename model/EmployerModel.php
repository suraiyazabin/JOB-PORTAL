<?php

// ============================================================
//  SHARED AUTH FUNCTIONS
// ============================================================

function emailExists($conn, $email)
{
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

function insertUser($conn, $name, $email, $passwordHash, $phone, $role)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (name, email, password_hash, phone, role)
         VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $passwordHash, $phone, $role);
    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $id;
}

function getUserByEmailAndRole($conn, $email, $role)
{
    if ($role === 'employer') {
        $stmt = mysqli_prepare($conn,
            "SELECT u.*, ep.id AS profile_id
             FROM users u
             JOIN employer_profiles ep ON u.id = ep.user_id
             WHERE u.email = ? AND u.role = 'employer'");
        mysqli_stmt_bind_param($stmt, "s", $email);
    } elseif ($role === 'recruiter') {
        $stmt = mysqli_prepare($conn,
            "SELECT u.*, rp.id AS profile_id
             FROM users u
             JOIN recruiter_profiles rp ON u.id = rp.user_id
             WHERE u.email = ? AND u.role = 'recruiter'");
        mysqli_stmt_bind_param($stmt, "s", $email);
    } else {
        // admin and seeker: no separate profile table, use user id as profile_id
        $stmt = mysqli_prepare($conn,
            "SELECT u.*, u.id AS profile_id FROM users u
             WHERE u.email = ? AND u.role = ?");
        mysqli_stmt_bind_param($stmt, "ss", $email, $role);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user   = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $user;
}

// ============================================================
//  EMPLOYER PROFILE FUNCTIONS
// ============================================================

function insertEmployerProfile($conn, $userId, $companyName, $industry, $companySize, $description, $website, $address, $logoPath)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO employer_profiles (user_id, company_name, industry, company_size, description, website, address, logo_path)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isssssss",
        $userId, $companyName, $industry, $companySize, $description, $website, $address, $logoPath);
    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $id;
}

function getEmployerProfile($conn, $profileId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT ep.*, u.name, u.email, u.phone, u.profile_pic, u.password_hash
         FROM employer_profiles ep
         JOIN users u ON ep.user_id = u.id
         WHERE ep.id = ?");
    mysqli_stmt_bind_param($stmt, "i", $profileId);
    mysqli_stmt_execute($stmt);
    $result  = mysqli_stmt_get_result($stmt);
    $profile = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $profile;
}

function updateEmployerProfile($conn, $profileId, $companyName, $industry, $companySize, $description, $website, $address, $logoPath, $name, $phone, $userId)
{
    $stmt = mysqli_prepare($conn,
        "UPDATE employer_profiles
         SET company_name=?, industry=?, company_size=?, description=?, website=?, address=?, logo_path=?
         WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sssssssi",
        $companyName, $industry, $companySize, $description, $website, $address, $logoPath, $profileId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt2 = mysqli_prepare($conn, "UPDATE users SET name=?, phone=? WHERE id=?");
    mysqli_stmt_bind_param($stmt2, "ssi", $name, $phone, $userId);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);
}

function changePassword($conn, $userId, $newHash)
{
    $stmt = mysqli_prepare($conn, "UPDATE users SET password_hash=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "si", $newHash, $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ============================================================
//  JOB FUNCTIONS (used by Employer)
// ============================================================

function getAllCategories($conn)
{
    $result = mysqli_query($conn, "SELECT id, name FROM categories ORDER BY name");
    $cats   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $cats[] = $row;
    }
    return $cats;
}

function getJobsByEmployer($conn, $employerProfileId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT j.*, c.name AS category_name,
                (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS app_count
         FROM jobs j
         LEFT JOIN categories c ON j.category_id = c.id
         WHERE j.employer_id = ?
         ORDER BY j.created_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $employerProfileId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $jobs   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $jobs;
}

function getJobById($conn, $jobId, $employerProfileId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT j.*, c.name AS category_name
         FROM jobs j
         LEFT JOIN categories c ON j.category_id = c.id
         WHERE j.id = ? AND j.employer_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $jobId, $employerProfileId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $job    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $job;
}

function createJob($conn, $employerProfileId, $categoryId, $title, $description, $requirements, $benefits, $salaryMin, $salaryMax, $location, $jobType, $experienceLevel, $deadline, $status)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO jobs (employer_id, category_id, title, description, requirements, benefits,
                           salary_min, salary_max, location, job_type, experience_level, deadline, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iissssddsssss",
        $employerProfileId, $categoryId, $title, $description, $requirements, $benefits,
        $salaryMin, $salaryMax, $location, $jobType, $experienceLevel, $deadline, $status);
    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $id;
}

function updateJob($conn, $jobId, $employerProfileId, $categoryId, $title, $description, $requirements, $benefits, $salaryMin, $salaryMax, $location, $jobType, $experienceLevel, $deadline, $status)
{
    $stmt = mysqli_prepare($conn,
        "UPDATE jobs
         SET category_id=?, title=?, description=?, requirements=?, benefits=?,
             salary_min=?, salary_max=?, location=?, job_type=?, experience_level=?, deadline=?, status=?
         WHERE id=? AND employer_id=?");
    mysqli_stmt_bind_param($stmt, "issssddsssssii",
        $categoryId, $title, $description, $requirements, $benefits,
        $salaryMin, $salaryMax, $location, $jobType, $experienceLevel, $deadline, $status,
        $jobId, $employerProfileId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function deleteJob($conn, $jobId, $employerProfileId)
{
    $stmt = mysqli_prepare($conn, "DELETE FROM jobs WHERE id=? AND employer_id=?");
    mysqli_stmt_bind_param($stmt, "ii", $jobId, $employerProfileId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function toggleJobStatus($conn, $jobId, $employerProfileId, $status)
{
    $stmt = mysqli_prepare($conn, "UPDATE jobs SET status=? WHERE id=? AND employer_id=?");
    mysqli_stmt_bind_param($stmt, "sii", $status, $jobId, $employerProfileId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function jobHasApplications($conn, $jobId)
{
    $stmt = mysqli_prepare($conn, "SELECT id FROM applications WHERE job_id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $jobId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $has = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $has;
}

// ============================================================
//  APPLICANT MANAGEMENT FUNCTIONS
// ============================================================

function getApplicationsByJob($conn, $jobId, $employerProfileId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT a.*, u.name AS seeker_name, u.email AS seeker_email, u.phone AS seeker_phone,
                sp.headline, sp.years_experience, sp.education_level, sp.skills,
                j.title AS job_title
         FROM applications a
         JOIN users u ON a.seeker_id = u.id
         LEFT JOIN seeker_profiles sp ON sp.user_id = u.id
         JOIN jobs j ON a.job_id = j.id
         WHERE a.job_id = ? AND j.employer_id = ?
         ORDER BY a.applied_at DESC");
    mysqli_stmt_bind_param($stmt, "ii", $jobId, $employerProfileId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $apps   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $apps[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $apps;
}

function getApplicationById($conn, $appId, $employerProfileId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT a.*, u.name AS seeker_name, u.email AS seeker_email, u.phone AS seeker_phone,
                u.profile_pic, sp.headline, sp.summary, sp.skills, sp.years_experience,
                sp.education_level, sp.current_salary, sp.expected_salary, sp.preferred_location,
                sp.resume_path AS profile_resume,
                j.title AS job_title, j.id AS job_id
         FROM applications a
         JOIN users u ON a.seeker_id = u.id
         LEFT JOIN seeker_profiles sp ON sp.user_id = u.id
         JOIN jobs j ON a.job_id = j.id
         WHERE a.id = ? AND j.employer_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $appId, $employerProfileId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $app    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $app;
}

function updateApplicationStatus($conn, $appId, $status, $employerProfileId)
{
    // Make sure employer owns the job this application belongs to
    $stmt = mysqli_prepare($conn,
        "UPDATE applications a
         JOIN jobs j ON a.job_id = j.id
         SET a.status = ?
         WHERE a.id = ? AND j.employer_id = ?");
    mysqli_stmt_bind_param($stmt, "sii", $status, $appId, $employerProfileId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function getShortlistedByEmployer($conn, $employerProfileId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT a.*, u.name AS seeker_name, u.email AS seeker_email,
                sp.headline, sp.skills, sp.years_experience,
                j.title AS job_title
         FROM applications a
         JOIN users u ON a.seeker_id = u.id
         LEFT JOIN seeker_profiles sp ON sp.user_id = u.id
         JOIN jobs j ON a.job_id = j.id
         WHERE j.employer_id = ? AND a.status IN ('shortlisted','interview')
         ORDER BY j.title, a.applied_at");
    mysqli_stmt_bind_param($stmt, "i", $employerProfileId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $list   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $list;
}

// ============================================================
//  MESSAGING
// ============================================================

function sendMessage($conn, $senderId, $recipientId, $applicationId, $body)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO messages (sender_id, recipient_id, application_id, body)
         VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiis", $senderId, $recipientId, $applicationId, $body);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ============================================================
//  COMPLAINTS
// ============================================================

function submitComplaint($conn, $submitterId, $subjectId, $description)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO complaints (submitter_id, subject_id, description)
         VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iis", $submitterId, $subjectId, $description);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ============================================================
//  ANALYTICS
// ============================================================

function getEmployerAnalyticsSummary($conn, $employerProfileId)
{
    $data = [];

    // Total jobs
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM jobs WHERE employer_id=?");
    mysqli_stmt_bind_param($stmt, "i", $employerProfileId);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $data['total_jobs'] = mysqli_fetch_assoc($r)['total'] ?? 0;
    mysqli_stmt_close($stmt);

    // Active jobs
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM jobs WHERE employer_id=? AND status='active'");
    mysqli_stmt_bind_param($stmt, "i", $employerProfileId);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $data['active_jobs'] = mysqli_fetch_assoc($r)['total'] ?? 0;
    mysqli_stmt_close($stmt);

    // Total applications
    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS total FROM applications a
         JOIN jobs j ON a.job_id = j.id
         WHERE j.employer_id=?");
    mysqli_stmt_bind_param($stmt, "i", $employerProfileId);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $data['total_applications'] = mysqli_fetch_assoc($r)['total'] ?? 0;
    mysqli_stmt_close($stmt);

    // Shortlisted
    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS total FROM applications a
         JOIN jobs j ON a.job_id = j.id
         WHERE j.employer_id=? AND a.status='shortlisted'");
    mysqli_stmt_bind_param($stmt, "i", $employerProfileId);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $data['shortlisted'] = mysqli_fetch_assoc($r)['total'] ?? 0;
    mysqli_stmt_close($stmt);

    // Interview
    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS total FROM applications a
         JOIN jobs j ON a.job_id = j.id
         WHERE j.employer_id=? AND a.status='interview'");
    mysqli_stmt_bind_param($stmt, "i", $employerProfileId);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $data['interview'] = mysqli_fetch_assoc($r)['total'] ?? 0;
    mysqli_stmt_close($stmt);

    // Hired (rejected as baseline)
    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS total FROM applications a
         JOIN jobs j ON a.job_id = j.id
         WHERE j.employer_id=? AND a.status='rejected'");
    mysqli_stmt_bind_param($stmt, "i", $employerProfileId);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $data['rejected'] = mysqli_fetch_assoc($r)['total'] ?? 0;
    mysqli_stmt_close($stmt);

    return $data;
}

function getApplicationsPerJob($conn, $employerProfileId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT j.title,
                COUNT(a.id) AS total,
                SUM(CASE WHEN a.status='submitted'   THEN 1 ELSE 0 END) AS submitted,
                SUM(CASE WHEN a.status='reviewed'    THEN 1 ELSE 0 END) AS reviewed,
                SUM(CASE WHEN a.status='shortlisted' THEN 1 ELSE 0 END) AS shortlisted,
                SUM(CASE WHEN a.status='interview'   THEN 1 ELSE 0 END) AS interview,
                SUM(CASE WHEN a.status='rejected'    THEN 1 ELSE 0 END) AS rejected
         FROM jobs j
         LEFT JOIN applications a ON j.id = a.job_id
         WHERE j.employer_id = ?
         GROUP BY j.id, j.title
         ORDER BY total DESC");
    mysqli_stmt_bind_param($stmt, "i", $employerProfileId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function getApplicationsOverTime($conn, $employerProfileId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT DATE(a.applied_at) AS day, COUNT(*) AS total
         FROM applications a
         JOIN jobs j ON a.job_id = j.id
         WHERE j.employer_id = ?
           AND a.applied_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
         GROUP BY DATE(a.applied_at)
         ORDER BY day ASC");
    mysqli_stmt_bind_param($stmt, "i", $employerProfileId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}