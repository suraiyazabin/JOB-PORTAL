<?php if (!$job): ?>
  <div class="alert alert-danger">Job not found.</div>
<?php else: ?>
<div class="page-header">
  <div>
    <a href="SeekerController.php?action=jobs" style="color:#5a7a9a;font-size:.85rem;">← Back to Jobs</a>
    <h1 style="margin-top:6px;"><?= htmlspecialchars($job['title']) ?></h1>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <button class="btn <?= $saved ? 'btn-warning' : 'btn-secondary' ?> save-btn" data-id="<?= $job['id'] ?>">
      <?= $saved ? '★ Saved' : '☆ Save Job' ?>
    </button>
  </div>
</div>

<?php if ($msg):   ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="two-col">
  <!-- LEFT: Job Details -->
  <div>
    <div class="card">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
        <?php if (!empty($job['logo_path'])): ?>
          <img src="../<?= htmlspecialchars($job['logo_path']) ?>" style="width:60px;height:60px;object-fit:contain;border:1px solid #eee;border-radius:4px;">
        <?php else: ?>
          <div style="width:60px;height:60px;background:#0d2a4e;border-radius:4px;display:flex;align-items:center;justify-content:center;color:#c8a84b;font-size:1.5rem;">🏢</div>
        <?php endif; ?>
        <div>
          <div style="font-size:1.1rem;font-weight:bold;color:#1a56a0;"><?= htmlspecialchars($job['company_name'] ?? 'Unknown Company') ?></div>
          <?php if ($job['industry']): ?><div style="color:#5a7a9a;font-size:.85rem;"><?= htmlspecialchars($job['industry']) ?></div><?php endif; ?>
        </div>
      </div>
      <table style="font-size:.88rem;width:100%;">
        <tr><td style="color:#5a7a9a;padding:5px 0;width:40%;">📍 Location</td><td><strong><?= htmlspecialchars($job['location']??'N/A') ?></strong></td></tr>
        <tr><td style="color:#5a7a9a;padding:5px 0;">💼 Job Type</td><td><span class="badge badge-secondary"><?= $job['job_type'] ?></span></td></tr>
        <tr><td style="color:#5a7a9a;padding:5px 0;">📊 Level</td><td><span class="badge badge-secondary"><?= $job['experience_level'] ?></span></td></tr>
        <tr><td style="color:#5a7a9a;padding:5px 0;">🏷 Category</td><td><?= htmlspecialchars($job['cat_name']??'—') ?></td></tr>
        <?php if ($job['salary_min'] || $job['salary_max']): ?>
        <tr><td style="color:#5a7a9a;padding:5px 0;">💰 Salary</td><td style="color:#2e7d32;font-weight:bold;">BDT <?= number_format($job['salary_min']) ?> – <?= number_format($job['salary_max']) ?> / yr</td></tr>
        <?php endif; ?>
        <?php if ($job['deadline']): ?>
        <tr><td style="color:#5a7a9a;padding:5px 0;">⏰ Deadline</td><td style="color:<?= strtotime($job['deadline']) < time() ? '#c62828' : '#1a2a3a' ?>;"><?= date('d M Y', strtotime($job['deadline'])) ?></td></tr>
        <?php endif; ?>
        <tr><td style="color:#5a7a9a;padding:5px 0;">📅 Posted</td><td><?= date('d M Y', strtotime($job['created_at'])) ?></td></tr>
      </table>
    </div>

    <?php if ($job['company_desc']): ?>
    <div class="card">
      <h2>About the Company</h2>
      <p><?= nl2br(htmlspecialchars($job['company_desc'])) ?></p>
      <?php if ($job['poster_name']): ?><p style="color:#5a7a9a;font-size:.85rem;">Contact: <?= htmlspecialchars($job['poster_name']) ?></p><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- RIGHT: Description + Apply -->
  <div>
    <div class="card">
      <h2>Job Description</h2>
      <p><?= nl2br(htmlspecialchars($job['description']??'')) ?></p>
      <?php if ($job['requirements']): ?>
        <h3 style="color:#0d2a4e;margin:16px 0 8px;font-size:1rem;">Requirements</h3>
        <p><?= nl2br(htmlspecialchars($job['requirements'])) ?></p>
      <?php endif; ?>
      <?php if ($job['benefits']): ?>
        <h3 style="color:#0d2a4e;margin:16px 0 8px;font-size:1rem;">Benefits</h3>
        <p><?= nl2br(htmlspecialchars($job['benefits'])) ?></p>
      <?php endif; ?>
    </div>

    <!-- APPLY SECTION -->
    <div class="card">
      <h2>Apply for this Job</h2>
      <?php if ($applied): ?>
        <div class="alert alert-success">✅ You have already applied for this position.</div>
        <a href="SeekerController.php?action=applications" class="btn btn-secondary btn-sm">View My Applications</a>
      <?php elseif ($job['status'] !== 'active'): ?>
        <div class="alert alert-warning">This job listing is no longer accepting applications.</div>
      <?php else: ?>
        <form method="POST" action="SeekerController.php?action=jobs" enctype="multipart/form-data">
          <input type="hidden" name="submit_action" value="apply_job">
          <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
          <div class="form-group">
            <label>Cover Letter</label>
            <textarea name="cover_letter" rows="5" placeholder="Write a compelling cover letter explaining why you're a great fit..."></textarea>
          </div>
          <div class="form-group">
            <label>Resume (PDF)</label>
            <input type="file" name="apply_resume" accept=".pdf">
            <small>Leave empty to use your profile resume</small>
          </div>
          <button type="submit" class="btn btn-primary btn-lg btn-block">Submit Application</button>
        </form>
      <?php endif; ?>
    </div>

    <!-- COMPLAINT -->
    <div class="card">
      <h2>Report this Listing</h2>
      <form method="POST" action="SeekerController.php?action=jobs">
        <input type="hidden" name="submit_action" value="submit_complaint">
        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
        <input type="hidden" name="subject_id" value="<?= $job['employer_id'] ?? $job['recruiter_id'] ?? 0 ?>">
        <div class="form-group"><textarea name="description" rows="3" placeholder="Describe why this listing is misleading or inappropriate..."></textarea></div>
        <button type="submit" class="btn btn-danger btn-sm">Submit Complaint</button>
      </form>
    </div>
  </div>
</div>

<script>
document.querySelector('.save-btn').addEventListener('click', function() {
  var btn = this;
  var xhr = new XMLHttpRequest();
  xhr.open('POST','SeekerController.php?action=toggle_save',true);
  xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
  xhr.onload = function(){
    var res = JSON.parse(xhr.responseText);
    if (res.status === 'saved') { btn.textContent = '★ Saved'; btn.classList.add('btn-warning'); btn.classList.remove('btn-secondary'); }
    else { btn.textContent = '☆ Save Job'; btn.classList.add('btn-secondary'); btn.classList.remove('btn-warning'); }
  };
  xhr.send('job_id=' + btn.dataset.id);
});
</script>
<?php endif; ?>