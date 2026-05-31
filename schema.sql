CREATE TABLE users (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone        VARCHAR(20),
    role         ENUM('seeker','employer','recruiter','admin') NOT NULL,
    profile_pic  VARCHAR(255),
    is_active    TINYINT(1) DEFAULT 1,
    is_verified  TINYINT(1) DEFAULT 0,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE seeker_profiles (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT NOT NULL UNIQUE,
    headline          VARCHAR(200),
    summary           TEXT,
    skills            TEXT,
    years_experience  INT DEFAULT 0,
    education_level   ENUM('high_school','diploma','bachelor','master','phd','other'),
    current_salary    DECIMAL(10,2),
    expected_salary   DECIMAL(10,2),
    preferred_location VARCHAR(150),
    resume_path       VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE employer_profiles (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL UNIQUE,
    company_name VARCHAR(150),
    industry     VARCHAR(100),
    company_size VARCHAR(50),
    description  TEXT,
    website      VARCHAR(255),
    address      TEXT,
    logo_path    VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE recruiter_profiles (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL UNIQUE,
    agency_name    VARCHAR(150),
    specialization VARCHAR(150),
    description    TEXT,
    website        VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE recruiter_clients (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    recruiter_id          INT NOT NULL,
    employer_id           INT,
    company_name_override VARCHAR(150),
    added_at              DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (employer_id)  REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE jobs (
    id               INT AUTO_INCREMENT PRIMARY KEY,
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
    job_type         ENUM('full-time','part-time','remote','contract') DEFAULT 'full-time',
    experience_level ENUM('entry','mid','senior') DEFAULT 'entry',
    deadline         DATE,
    status           ENUM('active','closed','draft') DEFAULT 'active',
    is_featured      TINYINT(1) DEFAULT 0,
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id)  REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id)  REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE applications (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    job_id       INT NOT NULL,
    seeker_id    INT NOT NULL,
    recruiter_id INT,
    cover_letter TEXT,
    resume_path  VARCHAR(255),
    status       ENUM('submitted','reviewed','shortlisted','interview','rejected','withdrawn') DEFAULT 'submitted',
    applied_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id)      REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (seeker_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE saved_jobs (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    user_id  INT NOT NULL,
    job_id   INT NOT NULL,
    saved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_save (user_id, job_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id)  REFERENCES jobs(id) ON DELETE CASCADE
);

CREATE TABLE job_alerts (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    seeker_id   INT NOT NULL,
    keyword     VARCHAR(150),
    category_id INT,
    location    VARCHAR(150),
    job_type    ENUM('full-time','part-time','remote','contract'),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seeker_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE recruiter_outreach (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    recruiter_id INT NOT NULL,
    seeker_id    INT NOT NULL,
    job_id       INT,
    message      TEXT,
    status       ENUM('sent','read','responded') DEFAULT 'sent',
    sent_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seeker_id)    REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id)       REFERENCES jobs(id) ON DELETE SET NULL
);

CREATE TABLE messages (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    sender_id      INT NOT NULL,
    recipient_id   INT NOT NULL,
    application_id INT,
    body           TEXT NOT NULL,
    sent_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_read        TINYINT(1) DEFAULT 0,
    FOREIGN KEY (sender_id)    REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE SET NULL
);

CREATE TABLE complaints (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    submitter_id INT NOT NULL,
    subject_id   INT NOT NULL,
    description  TEXT NOT NULL,
    status       ENUM('open','resolved') DEFAULT 'open',
    admin_note   TEXT,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submitter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id)   REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE announcements (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    admin_id   INT NOT NULL,
    title      VARCHAR(200) NOT NULL,
    body       TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT IGNORE INTO users (name, email, password_hash, role, is_active, is_verified)
VALUES ('Admin', 'admin@jobportal.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, 1);

INSERT IGNORE INTO users (name, email, password_hash, role, is_active, is_verified)
VALUES ('John Seeker', 'seeker@jobportal.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seeker', 1, 1);

INSERT IGNORE INTO categories (name, description) VALUES
('Technology', 'Software, hardware, IT and tech roles'),
('Marketing', 'Digital marketing, branding, and advertising'),
('Finance', 'Accounting, banking, and financial services'),
('Healthcare', 'Medical, nursing, and health-related roles'),
('Education', 'Teaching, training, and academic positions'),
('Engineering', 'Civil, mechanical, electrical engineering'),
('Design', 'Graphic design, UX/UI, and creative roles'),
('Sales', 'Sales representatives and business development'),
('Operations', 'Logistics, supply chain, and operations'),
('Human Resources', 'HR, recruitment, and people management');

INSERT IGNORE INTO users (name, email, password_hash, role, is_active, is_verified)
VALUES ('TechCorp HR', 'employer@jobportal.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 1, 1);

INSERT IGNORE INTO employer_profiles (user_id, company_name, industry, company_size, description, website, address)
SELECT id, 'TechCorp Solutions', 'Technology', '201-500', 'A leading software solutions company.', 'https://techcorp.com', 'Dhaka, Bangladesh'
FROM users WHERE email = 'employer@jobportal.com';

INSERT IGNORE INTO jobs (employer_id, category_id, title, description, requirements, benefits, salary_min, salary_max, location, job_type, experience_level, deadline, status, is_featured)
SELECT u.id, c.id,
  'Senior PHP Developer',
  'We are looking for an experienced PHP developer to join our growing team.',
  'PHP 7.4+, MySQL, MVC frameworks, REST APIs, 3+ years experience',
  'Health insurance, remote work, annual bonus',
  60000, 90000, 'Dhaka', 'full-time', 'senior', DATE_ADD(NOW(), INTERVAL 30 DAY), 'active', 1
FROM users u, categories c WHERE u.email = 'employer@jobportal.com' AND c.name = 'Technology';

INSERT IGNORE INTO jobs (employer_id, category_id, title, description, requirements, benefits, salary_min, salary_max, location, job_type, experience_level, deadline, status)
SELECT u.id, c.id,
  'Frontend Developer',
  'Join our design team and build beautiful user interfaces.',
  'HTML5, CSS3, JavaScript, React or Vue, 1+ years experience',
  'Flexible hours, learning budget, team events',
  35000, 55000, 'Remote', 'remote', 'mid', DATE_ADD(NOW(), INTERVAL 45 DAY), 'active'
FROM users u, categories c WHERE u.email = 'employer@jobportal.com' AND c.name = 'Technology';

INSERT IGNORE INTO jobs (employer_id, category_id, title, description, requirements, benefits, salary_min, salary_max, location, job_type, experience_level, deadline, status)
SELECT u.id, c.id,
  'Digital Marketing Specialist',
  'Drive our digital presence and grow our online brand.',
  'SEO, Google Ads, Social Media, Content Creation, 2+ years experience',
  'Performance bonus, work from home options',
  30000, 50000, 'Dhaka', 'full-time', 'mid', DATE_ADD(NOW(), INTERVAL 20 DAY), 'active'
FROM users u, categories c WHERE u.email = 'employer@jobportal.com' AND c.name = 'Marketing';