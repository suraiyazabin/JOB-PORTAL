<?php
$pageTitle  = ($view ?? 'dashboard') === 'profile' ? 'My Profile' : 'Dashboard';
$activePage = ($view ?? 'dashboard') === 'profile' ? 'profile'   : 'dashboard';
include __DIR__ . '/../header.php';

if (($view ?? 'dashboard') === 'profile'):
?>

<!-- ══ PROFILE EDIT ══════════════════════════════════════════ -->
<div class="page-header"><h1>My Profile</h1></div>

<div class="card">
    <h2>Personal Information &amp; Professional Profile</h2>
    <form action="SeekerDashboardController.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_profile">

        <div class="form-row">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" required
                       value="<?php echo htmlspecialchars($profile['name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone"
                       value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Profile Picture</label>
            <?php if (!empty($profile['profile_pic'])): ?>
                <img src="../<?php echo htmlspecialchars($profile['profile_pic']); ?>"
                     style="height:60px;width:60px;border-radius:50%;object-fit:cover;display:block;margin-bottom:8px;border:2px solid #e5e7eb">
            <?php endif; ?>
            <input type="file" name="profile_pic" accept="image/*">
        </div>

        <div class="form-group">
            <label>Professional Headline</label>
            <input type="text" name="headline"
                   value="<?php echo htmlspecialchars($profile['headline'] ?? ''); ?>"
                   placeholder="e.g. Full Stack Developer with 3 years experience">
        </div>

        <div class="form-group">
            <label>Professional Summary</label>
            <textarea name="summary" rows="4"
                      placeholder="Write a brief professional summary..."><?php echo htmlspecialchars($profile['summary'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label>Skills <small>(comma-separated)</small></label>
            <input type="text" name="skills"
                   value="<?php echo htmlspecialchars($profile['skills'] ?? ''); ?>"
                   placeholder="PHP, MySQL, JavaScript, HTML, CSS">
        </div>

        <div class="form-row-3">
            <div class="form-group">
                <label>Years of Experience</label>
                <input type="number" name="years_experience" min="0" max="50"
                       value="<?php echo $profile['years_experience'] ?? 0; ?>">
            </div>
            <div class="form-group">
                <label>Education Level</label>
                <select name="education_level">
                    <?php foreach (['', "High School", "Bachelor's", "Master's", "PhD"] as $edu): ?>
                        <option value="<?php echo $edu; ?>"
                            <?php echo ($profile['education_level'] ?? '') === $edu ? 'selected' : ''; ?>>
                            <?php echo $edu ?: '— Select —'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Preferred Location</label>
                <input type="text" name="preferred_location"
                       value="<?php echo htmlspecialchars($profile['preferred_location'] ?? ''); ?>"
                       placeholder="Dhaka, Bangladesh">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Current Salary (৳/month)</label>
                <input type="number" name="current_salary" min="0"
                       value="<?php echo $profile['current_salary'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label>Expected Salary (৳/month)</label>
                <input type="number" name="expected_salary" min="0"
                       value="<?php echo $profile['expected_salary'] ?? ''; ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Resume <small>(PDF only, max 5MB)</small></label>
            <?php if (!empty($profile['resume_path'])): ?>
                <div style="margin-bottom:8px">
                    <a href="../<?php echo htmlspecialchars($profile['resume_path']); ?>"
                       target="_blank" class="btn btn-sm btn-success">📄 View Current Resume</a>
                </div>
            <?php endif; ?>
            <input type="file" name="resume" accept=".pdf">
            <small>Upload new PDF to replace current resume.</small>
        </div>

        <button type="submit" class="btn btn-primary">Save Profile</button>
    </form>
</div>

<div class="card">
    <h2>Change Password</h2>
    <form action="SeekerDashboardController.php" method="post"
          onsubmit="return checkPassMatch()">
        <input type="hidden" name="action" value="change_password">
        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>New Password <small>(min 6)</small></label>
                <input type="password" name="new_password" id="newPass" required>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirmPass" required>
                <span class="err-msg" id="confirmPassErr"></span>
            </div>
        </div>
        <button type="submit" class="btn btn-warning">Change Password</button>
    </form>
</div>

<?php else: ?>
<!-- ══ DASHBOARD ════════════════════════════════════════════ -->
<div class="page-header">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Job Seeker'); ?>! 👋</h1>
    <a href="SeekerJobController.php" class="btn btn-primary">Browse Jobs →</a>
</div>

<div class="stats-grid">
    <div class="stat-card info">
        <div class="stat-value"><?php echo $stats['total_apps']; ?></div>
        <div class="stat-label">Applications Sent</div>
    </div>
    <div class="stat-card success">
        <div class="stat-value"><?php echo $stats['shortlisted']; ?></div>
        <div class="stat-label">Shortlisted</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-value"><?php echo $stats['interview']; ?></div>
        <div class="stat-label">Interview Stage</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $stats['saved']; ?></div>
        <div class="stat-label">Saved Jobs</div>
    </div>
</div>

<div class="card">
    <h2>Recent Applications</h2>
    <?php if (empty($recentApps)): ?>
        <p class="text-muted">No applications yet. <a href="SeekerJobController.php">Browse jobs</a> and apply!</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Job</th><th>Company</th><th>Applied</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php foreach ($recentApps as $app): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($app['job_title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($app['company_name'] ?? '—'); ?></td>
                    <td><small><?php echo date('d M Y', strtotime($app['applied_at'])); ?></small></td>
                    <td>
                        <span class="badge badge-<?php echo $app['status']; ?>">
                            <?php echo ucfirst($app['status']); ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-1">
        <a href="SeekerApplicationController.php" class="btn btn-secondary btn-sm">View All Applications →</a>
    </div>
    <?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    <a href="SeekerJobController.php" style="text-decoration:none">
        <div class="card" style="text-align:center;padding:28px;cursor:pointer"
             onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.12)'"
             onmouseout="this.style.boxShadow=''">
            <div style="font-size:2.2rem;margin-bottom:8px">🔍</div>
            <strong>Browse Jobs</strong>
            <p class="text-muted" style="font-size:.85rem;margin-top:4px">Search and filter active openings</p>
        </div>
    </a>
    <a href="SeekerApplicationController.php?view=saved" style="text-decoration:none">
        <div class="card" style="text-align:center;padding:28px;cursor:pointer"
             onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.12)'"
             onmouseout="this.style.boxShadow=''">
            <div style="font-size:2.2rem;margin-bottom:8px">🔖</div>
            <strong>Saved Jobs</strong>
            <p class="text-muted" style="font-size:.85rem;margin-top:4px"><?php echo $stats['saved']; ?> job(s) bookmarked</p>
        </div>
    </a>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../footer.php'; ?>