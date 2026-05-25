<?php

$pageTitle  = ($view ?? 'dashboard') === 'profile' ? 'My Profile' : 'Dashboard';
$activePage = ($view ?? 'dashboard') === 'profile' ? 'profile'   : 'dashboard';
include __DIR__ . '/../header.php';

// ── PROFILE VIEW ─────────────────────────────────────────────
if (($view ?? 'dashboard') === 'profile'):
?>

<div class="page-header">
    <h1>My Profile</h1>
</div>

<!-- Company & Personal Info -->
<div class="card">
    <h2>Company &amp; Personal Information</h2>
    <form action="EmployerDashboardController.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_profile">

        <div class="form-row">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name"
                       value="<?php echo htmlspecialchars($profile['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone"
                       value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Company Name *</label>
            <input type="text" name="company_name"
                   value="<?php echo htmlspecialchars($profile['company_name'] ?? ''); ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Industry</label>
                <input type="text" name="industry"
                       value="<?php echo htmlspecialchars($profile['industry'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Company Size</label>
                <select name="company_size">
                    <?php foreach (['','1-10','11-50','51-200','201-1000','1000+'] as $sz): ?>
                        <option value="<?php echo $sz; ?>"
                            <?php echo ($profile['company_size'] ?? '') === $sz ? 'selected' : ''; ?>>
                            <?php echo $sz ?: '— Select —'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Address *</label>
            <input type="text" name="address"
                   value="<?php echo htmlspecialchars($profile['address'] ?? ''); ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Website</label>
                <input type="url" name="website"
                       value="<?php echo htmlspecialchars($profile['website'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Company Logo</label>
                <?php if (!empty($profile['logo_path'])): ?>
                    <br>
                    <img src="../<?php echo htmlspecialchars($profile['logo_path']); ?>"
                         style="height:60px;margin-bottom:8px;border-radius:6px;border:1px solid #ddd">
                    <br>
                <?php endif; ?>
                <input type="file" name="logo" accept="image/*">
                <small>Leave blank to keep current logo.</small>
            </div>
        </div>

        <div class="form-group">
            <label>Company Description</label>
            <textarea name="description" rows="4"><?php echo htmlspecialchars($profile['description'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>

<!-- Change Password -->
<div class="card">
    <h2>Change Password</h2>
    <form action="EmployerDashboardController.php" method="post"
          onsubmit="return checkPassMatch()">
        <input type="hidden" name="action" value="change_password">

        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>New Password <small>(min 6 chars)</small></label>
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

<?php else: // ── DASHBOARD VIEW ──────────────────────────────── ?>

<div class="page-header">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Employer'); ?>! 👋</h1>
    <a href="JobController.php?action=create" class="btn btn-primary">+ Post New Job</a>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo $summary['total_jobs']; ?></div>
        <div class="stat-label">Total Jobs Posted</div>
    </div>
    <div class="stat-card success">
        <div class="stat-value"><?php echo $summary['active_jobs']; ?></div>
        <div class="stat-label">Active Jobs</div>
    </div>
    <div class="stat-card info">
        <div class="stat-value"><?php echo $summary['total_applications']; ?></div>
        <div class="stat-label">Total Applications</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-value"><?php echo $summary['shortlisted']; ?></div>
        <div class="stat-label">Shortlisted</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $summary['interview']; ?></div>
        <div class="stat-label">In Interview</div>
    </div>
</div>

<!-- Recent Jobs -->
<div class="card">
    <h2>Recent Job Postings</h2>
    <?php if (empty($recentJobs)): ?>
        <p class="text-muted">No job postings yet. <a href="JobController.php?action=create">Post your first job</a>.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Applications</th>
                    <th>Deadline</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach (array_slice($recentJobs, 0, 5) as $job): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($job['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($job['category_name'] ?? '—'); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $job['status']; ?>">
                            <?php echo ucfirst($job['status']); ?>
                        </span>
                    </td>
                    <td><?php echo $job['app_count']; ?></td>
                    <td><?php echo $job['deadline'] ? htmlspecialchars($job['deadline']) : '—'; ?></td>
                    <td>
                        <a href="ApplicantController.php?job_id=<?php echo $job['id']; ?>"
                           class="btn btn-sm btn-info">Applicants</a>
                        <a href="JobController.php?action=edit&id=<?php echo $job['id']; ?>"
                           class="btn btn-sm btn-warning">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (count($recentJobs) > 5): ?>
        <div class="mt-1">
            <a href="JobController.php" class="btn btn-secondary btn-sm">View All Jobs →</a>
        </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Shortlisted Candidates -->
<div class="card">
    <h2>Shortlisted &amp; Interview Candidates</h2>
    <?php if (empty($shortlisted)): ?>
        <p class="text-muted">No shortlisted candidates yet.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Headline</th>
                    <th>Applied For</th>
                    <th>Stage</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($shortlisted as $app): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($app['seeker_name']); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($app['seeker_email']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($app['headline'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($app['job_title']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $app['status']; ?>">
                            <?php echo ucfirst($app['status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="ApplicantController.php?action=view&id=<?php echo $app['id']; ?>"
                           class="btn btn-sm btn-primary">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<?php include __DIR__ . '/../footer.php'; ?>