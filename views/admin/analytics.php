<?php
$catLabels  = array_column($analytics['jobs_per_cat'], 'name');
$catCounts  = array_column($analytics['jobs_per_cat'], 'cnt');
$appMonths  = array_column($analytics['apps_monthly'], 'mo');
$appCounts  = array_column($analytics['apps_monthly'], 'cnt');
?>
<div class="page-header"><h1>Platform Analytics</h1></div>

<div class="two-col">
  <div class="card">
    <h2>Active Jobs by Category</h2>
    <canvas id="catChart"></canvas>
  </div>
  <div class="card">
    <h2>Application Volume (Last 6 Months)</h2>
    <canvas id="appChart"></canvas>
  </div>
</div>

<div class="card">
  <h2>Top Employers by Applications Received</h2>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Rank</th><th>Company</th><th>Total Applications</th></tr></thead>
      <tbody>
        <?php foreach ($analytics['top_employers'] as $i => $e): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($e['company_name']) ?></td>
          <td><strong><?= $e['cnt'] ?></strong></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($analytics['top_employers'])): ?><tr><td colspan="3" class="no-data">No data yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('catChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($catLabels) ?>,
    datasets: [{ label: 'Jobs', data: <?= json_encode($catCounts) ?>, backgroundColor: '#1a56a0', borderRadius: 4 }]
  },
  options: { responsive: true, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
new Chart(document.getElementById('appChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($appMonths) ?>,
    datasets: [{ label: 'Applications', data: <?= json_encode($appCounts) ?>, borderColor: '#c8a84b', backgroundColor: 'rgba(200,168,75,.1)', tension: 0.3, fill: true }]
  },
  options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>