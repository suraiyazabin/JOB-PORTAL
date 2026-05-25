<?php
// Variables from SeekerJobController: $jobs, $categories, $savedIds, $keyword, $categoryId, $location, $jobType, $expLevel

$pageTitle  = 'Browse Jobs';
$activePage = 'jobs';
include __DIR__ . '/../header.php';
?>

<div class="page-header">
    <h1>Browse Jobs</h1>
    <span class="text-muted"><?php echo count($jobs); ?> job(s) found</span>
</div>

<!-- Search + Filter Bar -->
<div class="card" style="padding:18px">
    <form action="SeekerJobController.php" method="get">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;align-items:flex-end">
            <div class="form-group" style="margin-bottom:0">
                <label>Search</label>
                <input type="text" name="keyword"
                       value="<?php echo htmlspecialchars($keyword ?? ''); ?>"
                       placeholder="Job title, skill, company...">
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label>Category</label>
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"
                            <?php echo ($categoryId ?? 0) == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label>Location</label>
                <input type="text" name="location"
                       value="<?php echo htmlspecialchars($location ?? ''); ?>"
                       placeholder="City...">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </div>

        <!-- Extra filters row -->
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:12px;margin-top:14px;align-items:flex-end">
            <div class="form-group" style="margin-bottom:0">
                <label>Job Type</label>
                <select name="job_type">
                    <option value="">Any Type</option>
                    <?php foreach (['full-time'=>'Full Time','part-time'=>'Part Time','remote'=>'Remote','contract'=>'Contract'] as $v=>$l): ?>
                        <option value="<?php echo $v; ?>" <?php echo ($jobType??'')===$v?'selected':''; ?>><?php echo $l; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label>Experience</label>
                <select name="exp_level">
                    <option value="">Any Level</option>
                    <option value="entry"  <?php echo ($expLevel??'')==='entry'  ?'selected':''; ?>>Entry</option>
                    <option value="mid"    <?php echo ($expLevel??'')==='mid'    ?'selected':''; ?>>Mid</option>
                    <option value="senior" <?php echo ($expLevel??'')==='senior' ?'selected':''; ?>>Senior</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label>Min Salary (৳)</label>
                <input type="number" name="sal_min" min="0"
                       value="<?php echo $salaryMin > 0 ? $salaryMin : ''; ?>"
                       placeholder="e.g. 30000">
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label>Max Salary (৳)</label>
                <input type="number" name="sal_max" min="0"
                       value="<?php echo $salaryMax > 0 ? $salaryMax : ''; ?>"
                       placeholder="e.g. 80000">
            </div>
            <div>
                <a href="SeekerJobController.php" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>
</div>

<!-- Job Cards -->
<?php if (empty($jobs)): ?>
    <div class="card text-center" style="padding:48px">
        <p class="text-muted" style="font-size:1.05rem">No jobs match your search criteria.</p>
        <a href="SeekerJobController.php" class="btn btn-primary" style="margin-top:14px">Clear Filters</a>
    </div>
<?php else: ?>
    <?php foreach ($jobs as $job): ?>
    <div class="card" style="padding:20px;margin-bottom:14px">
        <div class="flex-between">
            <div class="flex" style="gap:14px;align-items:flex-start">
                <?php if (!empty($job['company_logo'])): ?>
                    <img src="../<?php echo htmlspecialchars($job['company_logo']); ?>"
                         style="width:48px;height:48px;border-radius:8px;object-fit:cover;border:1px solid #e5e7eb;flex-shrink:0">
                <?php else: ?>
                    <div style="width:48px;height:48px;border-radius:8px;background:#1e3a5f;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:1.2rem;flex-shrink:0">
                        <?php echo strtoupper(substr($job['company_name'] ?? 'J', 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div>
                    <h3 style="font-size:1.05rem;color:#1e3a5f;margin-bottom:4px">
                        <a href="SeekerJobController.php?action=detail&id=<?php echo $job['id']; ?>"
                           style="color:#1e3a5f;text-decoration:none">
                            <?php echo htmlspecialchars($job['title']); ?>
                            <?php if ($job['is_featured']): ?>
                                <span class="badge badge-interview" style="font-size:.72rem;margin-left:6px">⭐ Featured</span>
                            <?php endif; ?>
                        </a>
                    </h3>
                    <div class="flex" style="gap:10px;font-size:.85rem;color:#6b7280;flex-wrap:wrap">
                        <span>🏢 <?php echo htmlspecialchars($job['company_name'] ?? 'Unknown'); ?></span>
                        <span>📍 <?php echo htmlspecialchars($job['location']); ?></span>
                        <span class="badge badge-draft" style="font-size:.78rem"><?php echo ucfirst(str_replace('-',' ',$job['job_type'])); ?></span>
                        <span class="badge badge-reviewed" style="font-size:.78rem"><?php echo ucfirst($job['experience_level']); ?></span>
                        <?php if ($job['category_name']): ?>
                            <span class="badge badge-submitted" style="font-size:.78rem"><?php echo htmlspecialchars($job['category_name']); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($job['salary_min'] || $job['salary_max']): ?>
                        <div style="margin-top:6px;font-size:.88rem;color:#059669;font-weight:600">
                            💰
                            <?php if ($job['salary_min'] && $job['salary_max']): ?>
                                ৳<?php echo number_format($job['salary_min']); ?> – ৳<?php echo number_format($job['salary_max']); ?>
                            <?php elseif ($job['salary_min']): ?>
                                From ৳<?php echo number_format($job['salary_min']); ?>
                            <?php else: ?>
                                Up to ৳<?php echo number_format($job['salary_max']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex" style="gap:8px;align-items:center;flex-shrink:0">
                <?php if ($job['deadline']): ?>
                    <?php $daysLeft = (int)((strtotime($job['deadline']) - time()) / 86400); ?>
                    <span class="text-muted" style="font-size:.82rem">
                        <?php if ($daysLeft <= 3): ?>
                            <span style="color:#dc2626">⚡ <?php echo $daysLeft; ?>d left</span>
                        <?php else: ?>
                            Deadline: <?php echo date('d M', strtotime($job['deadline'])); ?>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>

                <!-- Save/Unsave button -->
                <?php $isSaved = in_array($job['id'], $savedIds ?? []); ?>
                <button id="saveBtn_<?php echo $job['id']; ?>"
                        onclick="toggleSaveJob(<?php echo $job['id']; ?>, this)"
                        class="btn btn-sm <?php echo $isSaved ? 'btn-warning' : 'btn-outline'; ?>">
                    <?php echo $isSaved ? '🔖 Saved' : '+ Save'; ?>
                </button>

                <a href="SeekerJobController.php?action=detail&id=<?php echo $job['id']; ?>"
                   class="btn btn-sm btn-primary">View &amp; Apply</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
function toggleSaveJob(jobId, btn) {
    var xhr = new XMLHttpRequest();
    xhr.onload = function() {
        var res = JSON.parse(this.responseText);
        if (res.success) {
            if (res.saved) {
                btn.className = 'btn btn-sm btn-warning';
                btn.innerHTML = '🔖 Saved';
            } else {
                btn.className = 'btn btn-sm btn-outline';
                btn.innerHTML = '+ Save';
            }
        }
    };
    xhr.open('POST', 'SeekerJobController.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('action=toggle_save&job_id=' + jobId);
}
</script>

<?php include __DIR__ . '/../footer.php'; ?>