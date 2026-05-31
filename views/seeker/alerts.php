<div class="page-header"><h1>Job Alerts</h1></div>
<?php if ($msg):   ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="two-col">
  <div class="card">
    <h2>Create New Alert</h2>
    <form method="POST" action="SeekerController.php?action=alerts">
      <input type="hidden" name="submit_action" value="add_alert">
      <div class="form-group"><label>Keyword</label><input type="text" name="keyword" placeholder="e.g. PHP Developer"></div>
      <div class="form-group">
        <label>Category</label>
        <select name="category_id">
          <option value="">Any Category</option>
          <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Location</label><input type="text" name="location" placeholder="e.g. Dhaka, Remote"></div>
      <div class="form-group">
        <label>Job Type</label>
        <select name="job_type">
          <option value="">Any Type</option>
          <option value="full-time">Full-Time</option>
          <option value="part-time">Part-Time</option>
          <option value="remote">Remote</option>
          <option value="contract">Contract</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Create Alert</button>
    </form>
  </div>

  <div>
    <h2 style="color:#0d2a4e;margin-bottom:16px;font-family:Arial;">My Alerts (<?= count($alerts) ?>)</h2>
    <?php if (empty($alerts)): ?>
      <div class="card"><p class="no-data">No alerts set up yet.</p></div>
    <?php else: ?>
      <?php foreach ($alerts as $a): ?>
      <div class="card" style="margin-bottom:12px;">
        <div style="display:flex;justify-content:space-between;align-items:start;">
          <div>
            <?php if ($a['keyword']): ?><div style="font-weight:bold;color:#0d2a4e;">🔍 <?= htmlspecialchars($a['keyword']) ?></div><?php endif; ?>
            <div style="font-size:.83rem;color:#5a7a9a;margin-top:4px;">
              <?php if ($a['cat_name']): ?><span class="badge badge-secondary"><?= htmlspecialchars($a['cat_name']) ?></span><?php endif; ?>
              <?php if ($a['location']): ?><span>📍 <?= htmlspecialchars($a['location']) ?></span><?php endif; ?>
              <?php if ($a['job_type']): ?><span class="badge badge-secondary"><?= $a['job_type'] ?></span><?php endif; ?>
            </div>
            <div style="font-size:.78rem;color:#999;margin-top:4px;">Created <?= date('d M Y', strtotime($a['created_at'])) ?></div>
          </div>
          <form method="POST" action="SeekerController.php?action=alerts" onsubmit="return confirm('Delete this alert?')">
            <input type="hidden" name="submit_action" value="delete_alert">
            <input type="hidden" name="alert_id" value="<?= $a['id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
          </form>
        </div>
        <div style="margin-top:10px;">
          <a href="SeekerController.php?action=jobs<?= $a['keyword']?'&keyword='.urlencode($a['keyword']):'' ?><?= $a['category_id']?'&category='.$a['category_id']:'' ?><?= $a['location']?'&location='.urlencode($a['location']):'' ?><?= $a['job_type']?'&job_type='.$a['job_type']:'' ?>" class="btn btn-secondary btn-sm">🔍 Search Now</a>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>