<?php

// $action is set by JobController.php: 'list', 'create', or 'edit'
// $jobs, $categories, $editJob are set by the controller

if ($action === 'list') {
    $pageTitle  = 'My Job Postings';
    $activePage = 'jobs';
} else {
    $pageTitle  = ($action === 'edit') ? 'Edit Job' : 'Post New Job';
    $activePage = 'jobs';
}
include __DIR__ . '/../header.php';
?>

<?php if ($action === 'list'): ?>
<!-- ══════════════════════════════════════════════════════════
     JOB LIST
═══════════════════════════════════════════════════════════ -->

<div class="page-header">
    <h1>My Job Postings</h1>
    <a href="JobController.php?action=create" class="btn btn-primary">+ Post New Job</a>
</div>

<?php if (empty($jobs)): ?>
    <div class="card text-center" style="padding:48px">
        <p style="font-size:1.1rem;color:#6b7280;margin-bottom:18px">
            You haven't posted any jobs yet.
        </p>
        <a href="JobController.php?action=create" class="btn btn-primary">Post Your First Job</a>
    </div>

<?php else: ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Job Title</th>
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
                    <td>
                        <strong><?php echo htmlspecialchars($job['title']); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($job['location']); ?></small>
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
                        <a href="ApplicantController.php?job_id=<?php echo $job['id']; ?>">
                            <?php echo $job['app_count']; ?> applicant(s)
                        </a>
                    </td>
                    <td>
                        <?php
                        if ($job['deadline']) {
                            $days = (int)((strtotime($job['deadline']) - time()) / 86400);
                            echo htmlspecialchars($job['deadline']);
                            if ($days < 0) {
                                echo ' <span class="badge badge-rejected">Expired</span>';
                            } elseif ($days <= 3) {
                                echo ' <span class="badge badge-interview">' . $days . 'd left</span>';
                            }
                        } else {
                            echo '—';
                        }
                        ?>
                    </td>
                    <td>
                        <!-- Toggle status via AJAX -->
                        <?php if ($job['status'] !== 'draft'): ?>
                            <button id="toggleBtn_<?php echo $job['id']; ?>"
                                    class="btn btn-sm btn-<?php echo $job['status'] === 'active' ? 'warning' : 'success'; ?>"
                                    onclick="toggleJobStatus(<?php echo $job['id']; ?>, '<?php echo $job['status']; ?>', 'JobController.php')">
                                <?php echo $job['status'] === 'active' ? 'Close Job' : 'Repost'; ?>
                            </button>
                        <?php endif; ?>

                        <a href="JobController.php?action=edit&id=<?php echo $job['id']; ?>"
                           class="btn btn-sm btn-info">Edit</a>

                        <a href="ApplicantController.php?job_id=<?php echo $job['id']; ?>"
                           class="btn btn-sm btn-primary">Applicants</a>

                        <!-- Delete form -->
                        <form action="JobController.php" method="post" style="display:inline"
                              onsubmit="return confirm('Delete this job posting? This cannot be undone.')">
                            <input type="hidden" name="action"   value="delete">
                            <input type="hidden" name="job_id"   value="<?php echo $job['id']; ?>">
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
<!-- ══════════════════════════════════════════════════════════
     CREATE / EDIT FORM
═══════════════════════════════════════════════════════════ -->

<div class="page-header">
    <h1><?php echo ($action === 'edit') ? 'Edit Job' : 'Post New Job'; ?></h1>
    <a href="JobController.php" class="btn btn-secondary">← Back to Jobs</a>
</div>

<div class="card">
    <form action="JobController.php" method="post"
          onsubmit="return validateJobForm(this)" novalidate>

        <!-- Hidden fields -->
        <input type="hidden" name="action"
               value="<?php echo $action === 'edit' ? 'update' : 'save'; ?>">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="job_id" value="<?php echo $editJob['id']; ?>">
        <?php endif; ?>

        <!-- Title & Category -->
        <div class="form-row">
            <div class="form-group">
                <label>Job Title *</label>
                <input type="text" name="title"
                       value="<?php echo htmlspecialchars($editJob['title'] ?? ''); ?>"
                       placeholder="e.g. Senior PHP Developer">
                <span class="err-msg" id="jTitleErr"></span>
            </div>
            <div class="form-group">
                <label>Category *</label>
                <select name="category_id">
                    <option value="">— Select Category —</option>
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

        <!-- Location, Job Type, Experience Level -->
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
                    <?php foreach (['full-time' => 'Full Time', 'part-time' => 'Part Time', 'remote' => 'Remote', 'contract' => 'Contract'] as $val => $lbl): ?>
                        <option value="<?php echo $val; ?>"
                            <?php echo ($editJob['job_type'] ?? 'full-time') === $val ? 'selected' : ''; ?>>
                            <?php echo $lbl; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Experience Level</label>
                <select name="experience_level">
                    <?php foreach (['entry' => 'Entry Level', 'mid' => 'Mid Level', 'senior' => 'Senior Level'] as $val => $lbl): ?>
                        <option value="<?php echo $val; ?>"
                            <?php echo ($editJob['experience_level'] ?? 'entry') === $val ? 'selected' : ''; ?>>
                            <?php echo $lbl; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Salary Range -->
        <div class="form-row">
            <div class="form-group">
                <label>Minimum Salary (৳)</label>
                <input type="number" name="salary_min" min="0" step="1000"
                       value="<?php echo $editJob['salary_min'] ?? ''; ?>"
                       placeholder="e.g. 30000">
                <span class="err-msg" id="jSalaryErr"></span>
            </div>
            <div class="form-group">
                <label>Maximum Salary (৳)</label>
                <input type="number" name="salary_max" min="0" step="1000"
                       value="<?php echo $editJob['salary_max'] ?? ''; ?>"
                       placeholder="e.g. 60000">
            </div>
        </div>

        <!-- Deadline & Status -->
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
                    <option value="draft"  <?php echo ($editJob['status'] ?? 'draft') === 'draft'  ? 'selected' : ''; ?>>Save as Draft</option>
                    <option value="active" <?php echo ($editJob['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Publish Now</option>
                    <?php if (($editJob['status'] ?? '') === 'closed'): ?>
                        <option value="closed" selected>Closed</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label>Job Description</label>
            <textarea name="description" rows="5"
                      placeholder="Describe the role, responsibilities, and what makes it exciting..."><?php
                echo htmlspecialchars($editJob['description'] ?? '');
            ?></textarea>
        </div>

        <!-- Requirements -->
        <div class="form-group">
            <label>Requirements</label>
            <textarea name="requirements" rows="4"
                      placeholder="List required qualifications, skills, and experience..."><?php
                echo htmlspecialchars($editJob['requirements'] ?? '');
            ?></textarea>
        </div>

        <!-- Benefits -->
        <div class="form-group">
            <label>Benefits</label>
            <textarea name="benefits" rows="3"
                      placeholder="Health insurance, annual bonus, flexible hours..."><?php
                echo htmlspecialchars($editJob['benefits'] ?? '');
            ?></textarea>
        </div>

        <!-- Submit buttons -->
        <div class="flex" style="gap:12px;margin-top:8px">
            <button type="submit" class="btn btn-primary">
                <?php echo $action === 'edit' ? '💾 Update Job' : '🚀 Save Job'; ?>
            </button>
            <a href="JobController.php" class="btn btn-secondary">Cancel</a>
        </div>

    </form>
</div>

<?php endif; ?>

<?php include __DIR__ . '/../footer.php'; ?>