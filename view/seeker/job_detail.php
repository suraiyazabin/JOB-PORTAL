<?php
// Variables: $job, $alreadyApplied, $alreadySaved, $profile

$pageTitle  = htmlspecialchars($job['title']);
$activePage = 'jobs';
include __DIR__ . '/../header.php';
?>

<div class="page-header">
    <div>
        <a href="SeekerJobController.php" style="color:#6b7280;font-size:.9rem">← Back to Jobs</a>
        <h1 style="margin-top:4px"><?php echo htmlspecialchars($job['title']); ?></h1>
    </div>
    <div class="flex" style="gap:10px">
        <?php if (!$alreadySaved): ?>
            <form action="SeekerJobController.php" method="post" style="display:inline">
                <input type="hidden" name="action" value="toggle_save">
                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                <button type="submit" class="btn btn-outline">🔖 Save Job</button>
            </form>
        <?php else: ?>
            <form action="SeekerApplicationController.php" method="post" style="display:inline">
                <input type="hidden" name="action" value="unsave">
                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                <button type="submit" class="btn btn-warning btn-sm">🔖 Saved</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:22px">

<!-- LEFT: Job Details -->
<div>
    <!-- Company header -->
    <div class="card" style="padding:20px">
        <div class="flex" style="gap:16px;align-items:center">
            <?php if (!empty($job['company_logo'])): ?>
                <img src="../<?php echo htmlspecialchars($job['company_logo']); ?>"
                     style="width:64px;height:64px;border-radius:10px;object-fit:cover;border:1px solid #e5e7eb">
            <?php else: ?>
                <div style="width:64px;height:64px;border-radius:10px;background:#1e3a5f;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.6rem;font-weight:700">
                    <?php echo strtoupper(substr($job['company_name'] ?? 'J', 0, 1)); ?>
                </div>
            <?php endif; ?>
            <div>
                <h2 style="font-size:1.1rem;color:#1e3a5f;margin-bottom:4px">
                    <?php echo htmlspecialchars($job['company_name'] ?? 'Unknown Company'); ?>
                </h2>
                <?php if ($job['industry']): ?>
                    <p class="text-muted" style="font-size:.88rem"><?php echo htmlspecialchars($job['industry']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Job meta badges -->
        <div class="flex" style="flex-wrap:wrap;gap:8px;margin-top:16px">
            <span class="badge badge-submitted">📍 <?php echo htmlspecialchars($job['location']); ?></span>
            <span class="badge badge-draft"><?php echo ucfirst(str_replace('-',' ',$job['job_type'])); ?></span>
            <span class="badge badge-reviewed"><?php echo ucfirst($job['experience_level']); ?> Level</span>
            <?php if ($job['category_name']): ?>
                <span class="badge badge-shortlisted"><?php echo htmlspecialchars($job['category_name']); ?></span>
            <?php endif; ?>
            <?php if ($job['is_featured']): ?>
                <span class="badge badge-interview">⭐ Featured</span>
            <?php endif; ?>
        </div>

        <?php if ($job['salary_min'] || $job['salary_max']): ?>
            <div style="margin-top:12px;font-size:1rem;color:#059669;font-weight:600">
                💰
                <?php if ($job['salary_min'] && $job['salary_max']): ?>
                    ৳<?php echo number_format($job['salary_min']); ?> – ৳<?php echo number_format($job['salary_max']); ?>/month
                <?php elseif ($job['salary_min']): ?>
                    From ৳<?php echo number_format($job['salary_min']); ?>/month
                <?php else: ?>
                    Up to ৳<?php echo number_format($job['salary_max']); ?>/month
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($job['deadline']): ?>
            <p class="text-muted" style="margin-top:8px;font-size:.87rem">
                📅 Application Deadline: <strong><?php echo date('d F Y', strtotime($job['deadline'])); ?></strong>
            </p>
        <?php endif; ?>
    </div>

    <!-- Description -->
    <?php if ($job['description']): ?>
    <div class="card">
        <h2>Job Description</h2>
        <p style="white-space:pre-wrap;line-height:1.8"><?php echo htmlspecialchars($job['description']); ?></p>
    </div>
    <?php endif; ?>

    <!-- Requirements -->
    <?php if ($job['requirements']): ?>
    <div class="card">
        <h2>Requirements</h2>
        <p style="white-space:pre-wrap;line-height:1.8"><?php echo htmlspecialchars($job['requirements']); ?></p>
    </div>
    <?php endif; ?>

    <!-- Benefits -->
    <?php if ($job['benefits']): ?>
    <div class="card">
        <h2>Benefits</h2>
        <p style="white-space:pre-wrap;line-height:1.8"><?php echo htmlspecialchars($job['benefits']); ?></p>
    </div>
    <?php endif; ?>

    <!-- Company Info -->
    <?php if ($job['company_desc'] || $job['company_website'] || $job['company_address']): ?>
    <div class="card">
        <h2>About <?php echo htmlspecialchars($job['company_name'] ?? 'the Company'); ?></h2>
        <?php if ($job['company_desc']): ?>
            <p><?php echo htmlspecialchars($job['company_desc']); ?></p>
        <?php endif; ?>
        <div class="flex" style="gap:14px;margin-top:10px;flex-wrap:wrap;font-size:.88rem">
            <?php if ($job['company_website']): ?>
                <a href="<?php echo htmlspecialchars($job['company_website']); ?>" target="_blank">
                    🌐 Website
                </a>
            <?php endif; ?>
            <?php if ($job['company_address']): ?>
                <span>📍 <?php echo htmlspecialchars($job['company_address']); ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- RIGHT: Apply Panel -->
<div>
    <?php if ($alreadyApplied): ?>
    <div class="card">
        <div class="alert alert-success" style="margin-bottom:0">
            ✅ You have already applied for this job.
            <br><br>
            <a href="SeekerApplicationController.php" class="btn btn-sm btn-info">Track Application</a>
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <h2>Apply for this Job</h2>
        <form action="SeekerJobController.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="apply">
            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">

            <div class="form-group">
                <label>Cover Letter</label>
                <textarea name="cover_letter" rows="6"
                          placeholder="Introduce yourself and explain why you're a great fit..."></textarea>
            </div>

            <div class="form-group">
                <label>Resume <small>(PDF only, max 5MB)</small></label>
                <?php if (!empty($profile['resume_path'])): ?>
                    <div class="alert alert-info" style="font-size:.85rem;padding:10px;margin-bottom:8px">
                        ✅ Your profile resume will be used automatically.<br>
                        Upload a new file below to override.
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger" style="font-size:.85rem;padding:10px;margin-bottom:8px">
                        ⚠️ No resume in your profile. Please upload one.
                    </div>
                <?php endif; ?>
                <input type="file" name="resume" accept=".pdf">
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%"
                    onclick="return confirm('Submit application for: <?php echo htmlspecialchars($job['title']); ?>?')">
                🚀 Submit Application
            </button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Quick Info card -->
    <div class="card">
        <h2>Quick Info</h2>
        <div style="font-size:.88rem;line-height:2">
            <div><strong>Posted by:</strong>
                <?php if ($job['recruiter_id']): ?>
                    <?php echo htmlspecialchars($job['agency_name'] ?? 'Recruiter'); ?> <small>(Agency)</small>
                <?php else: ?>
                    <?php echo htmlspecialchars($job['company_name'] ?? '—'); ?>
                <?php endif; ?>
            </div>
            <div><strong>Job Type:</strong> <?php echo ucfirst(str_replace('-',' ',$job['job_type'])); ?></div>
            <div><strong>Level:</strong> <?php echo ucfirst($job['experience_level']); ?></div>
            <?php if ($job['deadline']): ?>
                <div><strong>Deadline:</strong> <?php echo date('d M Y', strtotime($job['deadline'])); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

</div>

<?php include __DIR__ . '/../footer.php'; ?>