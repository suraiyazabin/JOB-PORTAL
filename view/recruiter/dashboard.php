<?php

$pageTitle  = ($view ?? 'dashboard') === 'profile' ? 'My Profile' : 'Recruiter Dashboard';
$activePage = ($view ?? 'dashboard') === 'profile' ? 'profile'   : 'dashboard';
include __DIR__ . '/../header.php';

if (($view ?? 'dashboard') === 'profile'):
?>

<!-- ══ PROFILE VIEW ══════════════════════════════════════════ -->
<div class="page-header"><h1>My Recruiter Profile</h1></div>

<div class="card">
    <h2>Agency &amp; Personal Information</h2>
    <form action="RecruiterDashboardController.php" method="post">
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
            <label>Agency / Firm Name *</label>
            <input type="text" name="agency_name"
                   value="<?php echo htmlspecialchars($profile['agency_name'] ?? ''); ?>" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Specialization</label>
                <input type="text" name="specialization"
                       value="<?php echo htmlspecialchars($profile['specialization'] ?? ''); ?>"
                       placeholder="Tech, Finance, Marketing...">
            </div>
            <div class="form-group">
                <label>Website</label>
                <input type="url" name="website"
                       value="<?php echo htmlspecialchars($profile['website'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="4"><?php echo htmlspecialchars($profile['description'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>

<div class="card">
    <h2>Change Password</h2>
    <form action="RecruiterDashboardController.php" method="post"
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

<?php else: ?>
<!-- ══ DASHBOARD VIEW ════════════════════════════════════════ -->

<div class="page-header">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Recruiter'); ?>! 🎯</h1>
    <a href="RecruiterJobController.php?action=create" class="btn btn-primary">+ Post New Job</a>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo $analytics['total_clients']; ?></div>
        <div class="stat-label">Active Clients</div>
    </div>
    <div class="stat-card success">
        <div class="stat-value"><?php echo $analytics['total_jobs']; ?></div>
        <div class="stat-label">Jobs Posted</div>
    </div>
    <div class="stat-card info">
        <div class="stat-value"><?php echo $analytics['total_applications']; ?></div>
        <div class="stat-label">Applications Managed</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-value"><?php echo $analytics['total_outreach']; ?></div>
        <div class="stat-label">Outreach Sent</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $analytics['response_rate']; ?>%</div>
        <div class="stat-label">Response Rate</div>
    </div>
</div>

<!-- Pipeline Snapshot -->
<div class="card">
    <h2>Pipeline Snapshot</h2>
    <?php if (empty($pipeline)): ?>
        <p class="text-muted">No pipeline activity yet.</p>
    <?php else: ?>
    <!-- Kanban-style columns -->
    <div class="pipeline-board">
        <?php
        $stages = [
            'submitted'   => 'Submitted',
            'reviewed'    => 'Reviewed',
            'shortlisted' => 'Shortlisted',
            'interview'   => 'Interview',
            'rejected'    => 'Rejected',
        ];
        // Group pipeline apps by status
        $grouped = [];
        foreach ($pipeline as $app) {
            $grouped[$app['status']][] = $app;
        }
        ?>
        <?php foreach ($stages as $stage => $label): ?>
        <div class="pipeline-col">
            <div class="pipeline-col-header">
                <span><?php echo $label; ?></span>
                <span style="background:#fff;padding:2px 8px;border-radius:12px;font-size:.8rem">
                    <?php echo count($grouped[$stage] ?? []); ?>
                </span>
            </div>
            <?php foreach (array_slice($grouped[$stage] ?? [], 0, 3) as $app): ?>
                <div class="pipeline-card">
                    <strong><?php echo htmlspecialchars($app['seeker_name']); ?></strong>
                    <small><?php echo htmlspecialchars($app['job_title']); ?></small><br>
                    <small class="text-muted"><?php echo htmlspecialchars($app['client_name'] ?? ''); ?></small>
                </div>
            <?php endforeach; ?>
            <?php if (count($grouped[$stage] ?? []) > 3): ?>
                <a href="PipelineController.php?status=<?php echo $stage; ?>"
                   style="font-size:.82rem;color:#2563eb;display:block;text-align:center;margin-top:6px">
                    +<?php echo count($grouped[$stage]) - 3; ?> more →
                </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <div style="margin-top:10px">
        <a href="PipelineController.php" class="btn btn-secondary btn-sm">View Full Pipeline →</a>
    </div>
    <?php endif; ?>
</div>

<!-- Clients Overview -->
<div class="card">
    <h2>Client Companies</h2>
    <?php if (empty($clients)): ?>
        <p class="text-muted">No clients added yet. <a href="ClientController.php?action=add">Add your first client</a>.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Company</th><th>Industry</th><th>Jobs Posted</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach (array_slice($clients, 0, 5) as $client): ?>
                <tr>
                    <td>
                        <?php if (!empty($client['logo_path'])): ?>
                            <img src="../<?php echo htmlspecialchars($client['logo_path']); ?>"
                                 style="height:28px;border-radius:4px;margin-right:8px;vertical-align:middle">
                        <?php endif; ?>
                        <strong>
                            <?php echo htmlspecialchars($client['company_name_override'] ?: ($client['registered_company_name'] ?? 'Unknown')); ?>
                        </strong>
                    </td>
                    <td><?php echo htmlspecialchars($client['industry'] ?? '—'); ?></td>
                    <td><?php echo $client['job_count'] ?? 0; ?></td>
                    <td>
                        <a href="RecruiterJobController.php?action=create&client_id=<?php echo $client['id']; ?>"
                           class="btn btn-sm btn-primary">Post Job</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-1">
        <a href="ClientController.php" class="btn btn-secondary btn-sm">Manage Clients →</a>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<?php include __DIR__ . '/../footer.php'; ?>