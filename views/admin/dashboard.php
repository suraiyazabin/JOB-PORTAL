<?php
$months = array_column($monthly, 'mo');
$counts = array_column($monthly, 'c');
?>
<div class="page-header">
  <h1>Admin Dashboard</h1>
  <span style="color:#5a7a9a;font-size:.9rem;"><?= date('l, d F Y') ?></span>
</div>
<?php if ($msg):   ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="stats-grid">
  <div class="stat-card"><div class="stat-value"><?= $stats['seekers'] ?></div><div class="stat-label">Job Seekers</div></div>
  <div class="stat-card"><div class="stat-value"><?= $stats['employers'] ?></div><div class="stat-label">Employers</div></div>
  <div class="stat-card"><div class="stat-value"><?= $stats['recruiters'] ?></div><div class="stat-label">Recruiters</div></div>
  <div class="stat-card"><div class="stat-value"><?= $stats['active_jobs'] ?></div><div class="stat-label">Active Jobs</div></div>
  <div class="stat-card"><div class="stat-value"><?= $stats['apps_today'] ?></div><div class="stat-label">Apps Today</div></div>
  <div class="stat-card danger"><div class="stat-value"><?= $stats['pending_verifications'] ?></div><div class="stat-label">Pending Verifications</div></div>
  <div class="stat-card danger"><div class="stat-value"><?= $stats['open_complaints'] ?></div><div class="stat-label">Open Complaints</div></div>
  <div class="stat-card"><div class="stat-value"><?= $stats['categories'] ?></div><div class="stat-label">Categories</div></div>
</div>

<div class="two-col">
  <div class="card">
    <h2>New Registrations (Last 6 Months)</h2>
    <canvas id="regChart"></canvas>
  </div>
  <div class="card">
    <h2>Recent Activity</h2>
    <table>
      <thead><tr><th>Name</th><th>Role</th><th>Joined</th></tr></thead>
      <tbody>
        <?php foreach ($activity as $a): ?>
        <tr>
          <td><?= htmlspecialchars($a['name']) ?></td>
          <td><span class="badge badge-secondary"><?= $a['role'] ?></span></td>
          <td><?= date('d M Y', strtotime($a['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($activity)): ?><tr><td colspan="3" class="no-data">No activity yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="two-col">
  <div class="card">
    <h2>Quick Actions</h2>
    <div style="display:flex;flex-wrap:wrap;gap:10px;">
      <a href="AdminController.php?action=employers&filter=pending" class="btn btn-warning">Pending Employers (<?= $stats['pending_verifications'] ?>)</a>
      <a href="AdminController.php?action=complaints&filter=open"   class="btn btn-danger">Open Complaints (<?= $stats['open_complaints'] ?>)</a>
      <a href="AdminController.php?action=categories"               class="btn btn-primary">Manage Categories</a>
      <a href="AdminController.php?action=announcements"            class="btn btn-secondary">Post Announcement</a>
    </div>
  </div>
  <div class="card">
    <h2>Platform Summary</h2>
    <table>
      <tbody>
        <tr><td>Total Users</td><td><strong><?= $stats['seekers']+$stats['employers']+$stats['recruiters'] ?></strong></td></tr>
        <tr><td>Active Job Listings</td><td><strong><?= $stats['active_jobs'] ?></strong></td></tr>
        <tr><td>Applications Today</td><td><strong><?= $stats['apps_today'] ?></strong></td></tr>
        <tr><td>Job Categories</td><td><strong><?= $stats['categories'] ?></strong></td></tr>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('regChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($months) ?>,
    datasets: [{ label: 'New Users', data: <?= json_encode($counts) ?>, backgroundColor: '#1a56a0', borderRadius: 4 }]
  },
  options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>