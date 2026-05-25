<?php
// $view: 'applications' | 'saved'
// $applications: array when view=applications
// $savedJobs: array when view=saved

$pageTitle  = $view === 'saved' ? 'Saved Jobs' : 'My Applications';
$activePage = 'applications';
include __DIR__ . '/../header.php';
?>

<!-- Tab Navigation -->
<div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid #e5e7eb;padding-bottom:0">
    <a href="SeekerApplicationController.php"
       style="padding:10px 20px;border-radius:6px 6px 0 0;text-decoration:none;font-weight:600;font-size:.9rem;
              background:<?php echo $view === 'applications' ? '#1e3a5f' : '#f3f4f6'; ?>;
              color:<?php echo $view === 'applications' ? '#fff' : '#4b5563'; ?>;
              border:2px solid <?php echo $view === 'applications' ? '#1e3a5f' : '#e5e7eb'; ?>;
              border-bottom:none;margin-bottom:-2px">
        📋 My Applications
    </a>
    <a href="SeekerApplicationController.php?view=saved"
       style="padding:10px 20px;border-radius:6px 6px 0 0;text-decoration:none;font-weight:600;font-size:.9rem;
              background:<?php echo $view === 'saved' ? '#1e3a5f' : '#f3f4f6'; ?>;
              color:<?php echo $view === 'saved' ? '#fff' : '#4b5563'; ?>;
              border:2px solid <?php echo $view === 'saved' ? '#1e3a5f' : '#e5e7eb'; ?>;
              border-bottom:none;margin-bottom:-2px">
        🔖 Saved Jobs
    </a>
</div>

<?php if ($view === 'applications'): ?>
<!-- ══ APPLICATIONS LIST ════════════════════════════════════ -->

<div class="page-header" style="margin-top:0">
    <h1>My Applications</h1>
    <a href="SeekerJobController.php" class="btn btn-primary">Browse More Jobs</a>
</div>

<?php if (empty($applications)): ?>
    <div class="card text-center" style="padding:48px">
        <p style="font-size:1.1rem;color:#6b7280;margin-bottom:16px">
            You haven't applied to any jobs yet.
        </p>
        <a href="SeekerJobController.php" class="btn btn-primary">Browse Jobs</a>
    </div>
<?php else: ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Company</th>
                    <th>Type</th>
                    <th>Applied On</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($applications as $app): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($app['job_title']); ?></strong><br>
                        <small class="text-muted">📍 <?php echo htmlspecialchars($app['location'] ?? ''); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($app['company_name'] ?? '—'); ?></td>
                    <td>
                        <small><?php echo ucfirst(str_replace('-', ' ', $app['job_type'] ?? '')); ?></small>
                    </td>
                    <td>
                        <small><?php echo date('d M Y', strtotime($app['applied_at'])); ?></small>
                    </td>
                    <td>
                        <?php if ($app['deadline']): ?>
                            <?php $daysLeft = (int)((strtotime($app['deadline']) - time()) / 86400); ?>
                            <small style="color:<?php echo $daysLeft < 0 ? '#dc2626' : ($daysLeft <= 3 ? '#d97706' : '#6b7280'); ?>">
                                <?php echo date('d M Y', strtotime($app['deadline'])); ?>
                            </small>
                        <?php else: ?>
                            <small class="text-muted">—</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $app['status']; ?>">
                            <?php echo ucfirst($app['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($app['status'] === 'submitted'): ?>
                            <form action="SeekerApplicationController.php" method="post"
                                  onsubmit="return confirm('Withdraw this application?')">
                                <input type="hidden" name="action" value="withdraw">
                                <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-warning">Withdraw</button>
                            </form>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:.82rem">
                                <?php echo in_array($app['status'], ['rejected','withdrawn']) ? '—' : 'In Progress'; ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Application Stage Legend -->
<div class="card" style="padding:14px 20px">
    <strong style="font-size:.88rem;color:#4b5563">Status Guide: &nbsp;</strong>
    <?php foreach ([
        'submitted'   => 'Submitted — waiting for review',
        'reviewed'    => 'Employer has viewed your application',
        'shortlisted' => 'You have been shortlisted!',
        'interview'   => 'Interview invitation sent',
        'rejected'    => 'Not selected this time',
        'withdrawn'   => 'You withdrew this application',
    ] as $status => $desc): ?>
        <span class="badge badge-<?php echo $status; ?>" style="margin-right:6px" title="<?php echo $desc; ?>">
            <?php echo ucfirst($status); ?>
        </span>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php else: ?>
<!-- ══ SAVED JOBS ════════════════════════════════════════════ -->

<div class="page-header" style="margin-top:0">
    <h1>Saved Jobs</h1>
    <a href="SeekerJobController.php" class="btn btn-primary">Browse More Jobs</a>
</div>

<?php if (empty($savedJobs)): ?>
    <div class="card text-center" style="padding:48px">
        <p style="font-size:1.1rem;color:#6b7280;margin-bottom:16px">
            No saved jobs yet. Click "+ Save" on any job listing.
        </p>
        <a href="SeekerJobController.php" class="btn btn-primary">Browse Jobs</a>
    </div>
<?php else: ?>
    <?php foreach ($savedJobs as $job): ?>
    <div class="card" style="padding:18px;margin-bottom:12px">
        <div class="flex-between">
            <div>
                <h3 style="font-size:1.02rem;color:#1e3a5f;margin-bottom:4px">
                    <?php echo htmlspecialchars($job['title']); ?>
                </h3>
                <div class="flex" style="gap:10px;font-size:.85rem;color:#6b7280;flex-wrap:wrap;margin-top:4px">
                    <span>🏢 <?php echo htmlspecialchars($job['company_name'] ?? '—'); ?></span>
                    <span>📍 <?php echo htmlspecialchars($job['location']); ?></span>
                    <span class="badge badge-draft"><?php echo ucfirst(str_replace('-', ' ', $job['job_type'])); ?></span>
                    <?php if ($job['category_name']): ?>
                        <span class="badge badge-submitted"><?php echo htmlspecialchars($job['category_name']); ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($job['salary_min'] || $job['salary_max']): ?>
                    <div style="margin-top:6px;font-size:.88rem;color:#059669;font-weight:600">
                        💰
                        <?php if ($job['salary_min'] && $job['salary_max']): ?>
                            ৳<?php echo number_format($job['salary_min']); ?> – ৳<?php echo number_format($job['salary_max']); ?>
                        <?php elseif ($job['salary_max']): ?>
                            Up to ৳<?php echo number_format($job['salary_max']); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div style="margin-top:6px;font-size:.82rem;color:#9ca3af">
                    Saved: <?php echo date('d M Y', strtotime($job['saved_at'])); ?>
                    <?php if ($job['status'] !== 'active'): ?>
                        &nbsp;<span style="color:#dc2626">(Job is no longer active)</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex" style="gap:8px;flex-shrink:0">
                <?php if ($job['status'] === 'active'): ?>
                    <a href="SeekerJobController.php?action=detail&id=<?php echo $job['id']; ?>"
                       class="btn btn-sm btn-primary">View &amp; Apply</a>
                <?php endif; ?>
                <form action="SeekerApplicationController.php" method="post"
                      onsubmit="return confirm('Remove from saved jobs?')">
                    <input type="hidden" name="action" value="unsave">
                    <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php endif; ?>

<?php include __DIR__ . '/../footer.php'; ?>