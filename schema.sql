-- ============================================================
--  Job Portal — Schema for Employer & Recruiter Modules
--  Tables taken directly from project spec (P06 Job Portal)
--  Compatible with XAMPP / MySQL 5.7+
-- ============================================================

-- ── Shared user table (all roles use this) ─────────────────
CREATE TABLE IF NOT EXISTS users (
    id          INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone       VARCHAR(20),
    role        ENUM('seeker','employer','recruiter','admin') NOT NULL,
    profile_pic VARCHAR(255),
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Employer profile (company info) ────────────────────────
CREATE TABLE IF NOT EXISTS employer_profiles (
    id           INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    user_id      INT NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    industry     VARCHAR(100),
    company_size VARCHAR(50),
    description  TEXT,
    website      VARCHAR(255),
    address      TEXT,
    logo_path    VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Recruiter profile (agency info) ────────────────────────
CREATE TABLE IF NOT EXISTS recruiter_profiles (
    id             INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    user_id        INT NOT NULL,
    agency_name    VARCHAR(150) NOT NULL,
    specialization VARCHAR(100),
    description    TEXT,
    website        VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Recruiter → Client company relationships ────────────────
CREATE TABLE IF NOT EXISTS recruiter_clients (
    id                    INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    recruiter_id          INT NOT NULL,
    employer_id           INT,
    company_name_override VARCHAR(150),
    added_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recruiter_id) REFERENCES recruiter_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (employer_id)  REFERENCES employer_profiles(id) ON DELETE SET NULL
);

-- ── Job categories (managed by Admin, used by Employer/Recruiter) ──
CREATE TABLE IF NOT EXISTS categories (
    id          INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    name        VARCHAR(100) NOT NULL,
    description TEXT
);

-- ── Job postings ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS jobs (
    id               INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    employer_id      INT,
    recruiter_id     INT,
    category_id      INT,
    title            VARCHAR(200) NOT NULL,
    description      TEXT,
    requirements     TEXT,
    benefits         TEXT,
    salary_min       DECIMAL(10,2),
    salary_max       DECIMAL(10,2),
    location         VARCHAR(150),
    job_type         ENUM('full-time','part-time','remote','contract') NOT NULL DEFAULT 'full-time',
    experience_level ENUM('entry','mid','senior') NOT NULL DEFAULT 'entry',
    deadline         DATE,
    status           ENUM('active','closed','draft') NOT NULL DEFAULT 'draft',
    is_featured      TINYINT(1) NOT NULL DEFAULT 0,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id)  REFERENCES employer_profiles(id) ON DELETE SET NULL,
    FOREIGN KEY (recruiter_id) REFERENCES recruiter_profiles(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id)  REFERENCES categories(id) ON DELETE SET NULL
);

-- ── Job seeker extended profile (needed for recruiter candidate search) ──
CREATE TABLE IF NOT EXISTS seeker_profiles (
    id                INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    user_id           INT NOT NULL,
    headline          VARCHAR(200),
    summary           TEXT,
    skills            TEXT,
    years_experience  INT DEFAULT 0,
    education_level   VARCHAR(100),
    current_salary    DECIMAL(10,2),
    expected_salary   DECIMAL(10,2),
    preferred_location VARCHAR(150),
    resume_path       VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Job applications ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS applications (
    id           INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    job_id       INT NOT NULL,
    seeker_id    INT NOT NULL,
    recruiter_id INT,
    cover_letter TEXT,
    resume_path  VARCHAR(255),
    status       ENUM('submitted','reviewed','shortlisted','interview','rejected','withdrawn')
                 NOT NULL DEFAULT 'submitted',
    applied_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id)       REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (seeker_id)    REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recruiter_id) REFERENCES recruiter_profiles(id) ON DELETE SET NULL
);

-- ── Recruiter outreach to seekers ───────────────────────────
CREATE TABLE IF NOT EXISTS recruiter_outreach (
    id           INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    recruiter_id INT NOT NULL,
    seeker_id    INT NOT NULL,
    job_id       INT,
    message      TEXT NOT NULL,
    status       ENUM('sent','read','responded') NOT NULL DEFAULT 'sent',
    sent_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recruiter_id) REFERENCES recruiter_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (seeker_id)    REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id)       REFERENCES jobs(id) ON DELETE SET NULL
);

-- ── In-platform messages (employer ↔ applicant) ─────────────
CREATE TABLE IF NOT EXISTS messages (
    id             INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    sender_id      INT NOT NULL,
    recipient_id   INT NOT NULL,
    application_id INT,
    body           TEXT NOT NULL,
    sent_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read        TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (sender_id)      REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE SET NULL
);

-- ── Complaints (employer/recruiter submits to admin) ────────
CREATE TABLE IF NOT EXISTS complaints (
    id          INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    submitter_id INT NOT NULL,
    subject_id   INT NOT NULL,
    description  TEXT NOT NULL,
    status       ENUM('open','resolved') NOT NULL DEFAULT 'open',
    admin_note   TEXT,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submitter_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
--  Seed Data — Categories (Admin manages these in production,
--  but we seed a few for development)
-- ============================================================
INSERT IGNORE INTO categories (id, name, description) VALUES
  (1, 'Software Engineering',  'Development, programming, software roles'),
  (2, 'Design',                'UI/UX, graphic design, product design'),
  (3, 'Marketing',             'Digital marketing, SEO, content'),
  (4, 'Finance',               'Accounting, banking, financial analysis'),
  (5, 'Human Resources',       'Recruitment, HR management, talent'),
  (6, 'Sales',                 'Business development, account management'),
  (7, 'Operations',            'Supply chain, logistics, project management'),
  (8, 'Customer Support',      'Customer service, help desk, support');

-- ============================================================
--  Seed Admin user (for testing login gating)
--  password: admin123
-- ============================================================
INSERT IGNORE INTO users (id, name, email, password_hash, role, is_active, is_verified)
VALUES (1, 'Platform Admin', 'admin@jobportal.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin', 1, 1);

-- ============================================================
--  ALTER / ADD — Tables for Seeker and Admin modules
--  Run these after the initial schema if you already created it
-- ============================================================

CREATE TABLE IF NOT EXISTS saved_jobs (
    id       INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    user_id  INT NOT NULL,
    job_id   INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_saved (user_id, job_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id)  REFERENCES jobs(id)  ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS job_alerts (
    id          INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    seeker_id   INT NOT NULL,
    keyword     VARCHAR(150),
    category_id INT,
    location    VARCHAR(150),
    job_type    VARCHAR(50),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seeker_id)   REFERENCES users(id)       ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)  ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS announcements (
    id         INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    admin_id   INT NOT NULL,
    title      VARCHAR(255) NOT NULL,
    body       TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);
