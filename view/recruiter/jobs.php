<?php

$pageTitle  = ($action === 'list') ? 'Jobs Across Clients' : (($action === 'edit') ? 'Edit Job' : 'Post Job for Client');
$activePage = 'rec_jobs';
include __DIR__ . '/../header.php';
?>

<?php if ($action === 'list'): ?>
<!-- ══ JOBS LIST ══════════════════════════════════════════════ -->
<div class="page-header">
    <h1>Jobs Across Clients</h1>
    <a href="RecruiterJobController.php?action=create" class="btn btn-primary">+ Post New Job</a>
</div>

<?php if (empty($jobs)): ?>
    <div class="card text-center" style="padding:48px">
        <p class="text-muted" style="margin-bottom:16px">No jobs posted yet.</p>
        <a href="RecruiterJobController.php?action=create" class="btn btn-primary">Post First Job</a>
    </div>
<?php else: ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Client</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Applications</th>
                    <th>Deadline</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($jobs as $job): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($job['title']); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($job['location']); ?></small>
                    </td>
                    <td>
                        <?php if ($job['client_name']): ?>
                            <span class="badge badge-shortlisted"><?php echo htmlspecialchars($job['client_name']); ?></span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($job['category_name'] ?? '—'); ?></td>
                    <td><?php echo ucfirst(str_replace('-', ' ', $job['job_type'])); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $job['status']; ?>"
                              id="statusBadge_<?php echo $job['id']; ?>">
                            <?php echo ucfirst($job['status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="PipelineController.php?job_id=<?php echo $job['id']; ?>">
                            <?php echo $job['app_count']; ?> app(s)
                        </a>
                    </td>
                    <td><small><?php echo $job['deadline'] ?? '—'; ?></small></td>
                    <td>
                        <?php if ($job['status'] !== 'draft'): ?>
                            <button id="toggleBtn_<?php echo $job['id']; ?>"
                                    class="btn btn-sm btn-<?php echo $job['status'] === 'active' ? 'warning' : 'success'; ?>"
                                    onclick="toggleJobStatus(<?php echo $job['id']; ?>, '<?php echo $job['status']; ?>', 'RecruiterJobController.php')">
                                <?php echo $job['status'] === 'active' ? 'Close' : 'Repost'; ?>
                            </button>
                        <?php endif; ?>
                        <a href="RecruiterJobController.php?action=edit&id=<?php echo $job['id']; ?>"
                           class="btn btn-sm btn-info">Edit</a>
                        <form action="RecruiterJobController.php" method="post" style="display:inline"
                              onsubmit="return confirm('Delete this job?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<!-- ══ CREATE / EDIT FORM ══════════════════════════════════════ -->
<div class="page-header">
    <h1><?php echo $action === 'edit' ? 'Edit Job' : 'Post Job for Client'; ?></h1>
    <a href="RecruiterJobController.php" class="btn btn-secondary">← Back to Jobs</a>
</div>

<div class="card">
    <form action="RecruiterJobController.php" method="post"
          onsubmit="return validateJobForm(this)" novalidate>
        <input type="hidden" name="action"
               value="<?php echo $action === 'edit' ? 'update' : 'save'; ?>">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="job_id" value="<?php echo $editJob['id']; ?>">
        <?php endif; ?>

        <!-- Client Selector (create only) -->
        <?php if ($action !== 'edit'): ?>
        <div class="form-group">
            <label>Client Company *</label>
            <select name="client_id" required>
                <option value="">— Select Client —</option>
                <?php foreach ($clients as $cl): ?>
                    <option value="<?php echo $cl['id']; ?>"
                        <?php echo (isset($selectedClientId) && $selectedClientId == $cl['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cl['company_name_override'] ?: ($cl['registered_company_name'] ?? 'Unknown')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small>Select which client this job is being posted for.</small>
        </div>
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label>Job Title *</label>
                <input type="text" name="title"
                       value="<?php echo htmlspecialchars($editJob['title'] ?? ''); ?>"
                       placeholder="e.g. Senior Backend Developer">
                <span class="err-msg" id="jTitleErr"></span>
            </div>
            <div class="form-group">
                <label>Category *</label>
                <select name="category_id">
                    <option value="">— Select —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"
                            <?php echo ($editJob['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="err-msg" id="jCatErr"></span>
            </div>
        </div>

        <div class="form-row-3">
            <div class="form-group">
                <label>Location *</label>
                <input type="text" name="location"
                       value="<?php echo htmlspecialchars($editJob['location'] ?? ''); ?>"
                       placeholder="Dhaka, Bangladesh">
                <span class="err-msg" id="jLocErr"></span>
            </div>
            <div class="form-group">
                <label>Job Type</label>
                <select name="job_type">
                    <?php foreach (['full-time'=>'Full Time','part-time'=>'Part Time','remote'=>'Remote','contract'=>'Contract'] as $v=>$l): ?>
                        <option value="<?php echo $v; ?>" <?php echo ($editJob['job_type']??'full-time')===$v?'selected':''; ?>><?php echo $l; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Experience Level</label>
                <select name="experience_level">
                    <?php foreach (['entry'=>'Entry','mid'=>'Mid','senior'=>'Senior'] as $v=>$l): ?>
                        <option value="<?php echo $v; ?>" <?php echo ($editJob['experience_level']??'entry')===$v?'selected':''; ?>><?php echo $l; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Min Salary (৳)</label>
                <input type="number" name="salary_min" min="0"
                       value="<?php echo $editJob['salary_min'] ?? ''; ?>">
                <span class="err-msg" id="jSalaryErr"></span>
            </div>
            <div class="form-group">
                <label>Max Salary (৳)</label>
                <input type="number" name="salary_max" min="0"
                       value="<?php echo $editJob['salary_max'] ?? ''; ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Application Deadline *</label>
                <input type="date" name="deadline"
                       value="<?php echo htmlspecialchars($editJob['deadline'] ?? ''); ?>"
                       min="<?php echo date('Y-m-d'); ?>">
                <span class="err-msg" id="jDeadlineErr"></span>
            </div>
            <div class="form-group">
                <label>Publish Status</label>
                <select name="status">
                    <option value="draft"  <?php echo ($editJob['status']??'draft')==='draft'  ?'selected':''; ?>>Save as Draft</option>
                    <option value="active" <?php echo ($editJob['status']??'')==='active'?'selected':''; ?>>Publish Now</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Job Description</label>
            <textarea name="description" rows="5"><?php echo htmlspecialchars($editJob['description'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label>Requirements</label>
            <textarea name="requirements" rows="4"><?php echo htmlspecialchars($editJob['requirements'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label>Benefits</label>
            <textarea name="benefits" rows="3"><?php echo htmlspecialchars($editJob['benefits'] ?? ''); ?></textarea>
        </div>

        <div class="flex" style="gap:12px">
            <button type="submit" class="btn btn-primary">
                <?php echo $action === 'edit' ? '💾 Update Job' : '🚀 Post Job'; ?>
            </button>
            <a href="RecruiterJobController.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../footer.php'; ?>