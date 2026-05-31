<div class="page-header">
  <h1>My Dashboard</h1>
  <span style="color:#5a7a9a;font-size:.9rem;">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>!</span>
</div>
<?php if ($msg):   ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<?php if (empty($profile['headline'])): ?>
<div class="alert alert-warning">Your profile is incomplete. <a href="SeekerController.php?action=profile"><strong>Complete your profile</strong></a> to increase your chances of getting hired.</div>
<?php endif; ?>

<div class="stats-grid">
  <div class="stat-card"><div class="stat-value"><?= $dash['total_apps'] ?></div><div class="stat-label">Applications</div></div>
  <div class="stat-card success"><div class="stat-value"><?= $dash['shortlisted'] ?></div><div class="stat-label">Shortlisted</div></div>
  <div class="stat-card"><div class="stat-value"><?= $dash['saved'] ?></div><div class="stat-label">Saved Jobs</div></div>
  <div class="stat-card"><div class="stat-value"><?= $dash['alerts'] ?></div><div class="stat-label">Job Alerts</div></div>
</div>

<div class="two-col">
  <div class="card">
    <h2>Recent Applications</h2>
    <table>
      <thead><tr><th>Job</th><th>Company</th><th>Status</th><th>Applied</th></tr></thead>
      <tbody>
        <?php foreach ($dash['recent_apps'] as $a): ?>
        <tr>
          <td><a href="SeekerController.php?action=job_detail&id=<?= $a['job_id'] ?>"><?= htmlspecialchars($a['job_title']) ?></a></td>
          <td><?= htmlspecialchars($a['company_name'] ?? '—') ?></td>
          <td>
            <?php
            $sc = ['submitted'=>'badge-secondary','reviewed'=>'badge-under-investigation','shortlisted'=>'badge-status-released','interview'=>'badge-open','rejected'=>'badge-threat-high','withdrawn'=>'badge-status-deceased'];
            $cls = $sc[$a['status']] ?? 'badge-secondary';
            ?>
            <span class="badge <?= $cls ?>"><?= ucfirst($a['status']) ?></span>
          </td>
          <td><?= date('d M', strtotime($a['applied_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($dash['recent_apps'])): ?><tr><td colspan="4" class="no-data">No applications yet. <a href="SeekerController.php?action=jobs">Browse jobs</a></td></tr><?php endif; ?>
      </tbody>
    </table>
    <div style="margin-top:12px;"><a href="SeekerController.php?action=applications" class="btn btn-secondary btn-sm">View All Applications</a></div>
  </div>

  <div class="card">
    <h2>Saved Jobs</h2>
    <?php foreach ($dash['recent_saved'] as $j): ?>
    <div style="padding:10px 0;border-bottom:1px solid #eee;">
      <a href="SeekerController.php?action=job_detail&id=<?= $j['id'] ?>" style="font-weight:bold;color:#0d2a4e;"><?= htmlspecialchars($j['title']) ?></a>
      <div style="font-size:.82rem;color:#5a7a9a;"><?= htmlspecialchars($j['company_name'] ?? '—') ?> · <?= htmlspecialchars($j['location'] ?? '') ?></div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($dash['recent_saved'])): ?><p class="no-data">No saved jobs yet.</p><?php endif; ?>
    <div style="margin-top:12px;"><a href="SeekerController.php?action=saved_jobs" class="btn btn-secondary btn-sm">View All Saved</a></div>
  </div>
</div>

<div class="card">
  <h2>Quick Links</h2>
  <div style="display:flex;flex-wrap:wrap;gap:10px;">
    <a href="SeekerController.php?action=jobs"         class="btn btn-primary">🔍 Browse Jobs</a>
    <a href="SeekerController.php?action=profile"       class="btn btn-secondary">👤 Edit Profile</a>
    <a href="SeekerController.php?action=alerts"        class="btn btn-secondary">🔔 Manage Alerts</a>
    <a href="SeekerController.php?action=messages"      class="btn btn-secondary">💬 Messages</a>
  </div>
</div>