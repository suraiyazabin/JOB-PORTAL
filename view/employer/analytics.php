<?php

$pageTitle  = 'Recruitment Analytics';
$activePage = 'analytics';
include __DIR__ . '/../header.php';

// Build chart data for applications over time
$chartLabels = [];
$chartValues = [];
foreach ($appOverTime as $row) {
    $chartLabels[] = date('d M', strtotime($row['day']));
    $chartValues[] = (int)$row['total'];
}
$totalApps = $summary['total_applications'];
?>

<div class="page-header">
    <h1>Recruitment Analytics</h1>
    <a href="EmployerDashboardController.php" class="btn btn-secondary">← Dashboard</a>
</div>

<!-- Summary Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo $summary['total_jobs']; ?></div>
        <div class="stat-label">Total Jobs Posted</div>
    </div>
    <div class="stat-card success">
        <div class="stat-value"><?php echo $summary['active_jobs']; ?></div>
        <div class="stat-label">Active Jobs</div>
    </div>
    <div class="stat-card info">
        <div class="stat-value"><?php echo $summary['total_applications']; ?></div>
        <div class="stat-label">Total Applications</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-value"><?php echo $summary['shortlisted']; ?></div>
        <div class="stat-label">Shortlisted</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $summary['interview']; ?></div>
        <div class="stat-label">In Interview</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-value"><?php echo $summary['rejected']; ?></div>
        <div class="stat-label">Rejected</div>
    </div>
</div>

<!-- Overall Hiring Funnel -->
<div class="card">
    <h2>Overall Hiring Funnel</h2>
    <?php if ($totalApps == 0): ?>
        <p class="text-muted">No applications received yet.</p>
    <?php else: ?>
    <?php
    $stages = [
        'submitted'   => ['Submitted',   $summary['total_applications'], '#3b82f6'],
        'reviewed'    => ['Reviewed',    $summary['total_applications'] - ($summary['total_applications'] - ($summary['shortlisted'] + $summary['interview'] + $summary['rejected'])), '#6366f1'],
        'shortlisted' => ['Shortlisted', $summary['shortlisted'], '#10b981'],
        'interview'   => ['Interview',   $summary['interview'],   '#f59e0b'],
    ];
    $max = max(1, $totalApps);
    ?>
    <?php foreach ($stages as $key => [$label, $count, $color]): ?>
        <div class="funnel-row">
            <div class="funnel-label"><?php echo $label; ?></div>
            <div class="funnel-bar-wrap">
                <div class="funnel-bar <?php echo $key; ?>"
                     style="width:<?php echo round(($count / $max) * 100); ?>%;background:<?php echo $color; ?>">
                    <?php echo $count; ?>
                </div>
            </div>
            <div style="width:60px;color:#6b7280;font-size:.85rem">
                <?php echo $totalApps > 0 ? round(($count / $totalApps) * 100, 1) . '%' : '0%'; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Applications Over Time Chart -->
<div class="card">
    <h2>Applications Over Last 30 Days</h2>
    <?php if (empty($appOverTime)): ?>
        <p class="text-muted">No application data for the last 30 days.</p>
    <?php else: ?>
        <canvas id="appChart" style="max-height:250px;width:100%"></canvas>
    <?php endif; ?>
</div>

<!-- Per-Job Breakdown -->
<div class="card">
    <h2>Application Breakdown by Job</h2>
    <?php if (empty($appsByJob)): ?>
        <p class="text-muted">No jobs found.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Total Apps</th>
                    <th>Submitted</th>
                    <th>Reviewed</th>
                    <th>Shortlisted</th>
                    <th>Interview</th>
                    <th>Rejected</th>
                    <th>Conversion</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($appsByJob as $row): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                    <td><?php echo $row['total']; ?></td>
                    <td><?php echo $row['submitted']; ?></td>
                    <td><?php echo $row['reviewed']; ?></td>
                    <td><?php echo $row['shortlisted']; ?></td>
                    <td><?php echo $row['interview']; ?></td>
                    <td><?php echo $row['rejected']; ?></td>
                    <td>
                        <?php
                        $conv = $row['total'] > 0
                            ? round(($row['shortlisted'] / $row['total']) * 100, 1)
                            : 0;
                        $color = $conv >= 30 ? '#059669' : ($conv >= 10 ? '#d97706' : '#dc2626');
                        ?>
                        <span style="font-weight:700;color:<?php echo $color; ?>">
                            <?php echo $conv; ?>%
                        </span>
                        <small class="text-muted"> shortlist rate</small>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Chart JS (plain Canvas API — no framework, faculty style) -->
<?php if (!empty($appOverTime)): ?>
<script>
(function() {
    var labels = <?php echo json_encode($chartLabels); ?>;
    var values = <?php echo json_encode($chartValues); ?>;
    var canvas = document.getElementById('appChart');
    if (!canvas) return;
    var ctx    = canvas.getContext('2d');
    var W = canvas.offsetWidth || 700;
    var H = 220;
    canvas.width  = W;
    canvas.height = H;

    var pad = { top: 20, right: 20, bottom: 36, left: 44 };
    var chartW = W - pad.left - pad.right;
    var chartH = H - pad.top  - pad.bottom;
    var maxVal = Math.max.apply(null, values) || 1;

    ctx.fillStyle = '#f8fafc';
    ctx.fillRect(0, 0, W, H);

    // Grid lines
    ctx.strokeStyle = '#e5e7eb';
    ctx.lineWidth   = 1;
    for (var gi = 0; gi <= 4; gi++) {
        var y = pad.top + chartH - (gi / 4) * chartH;
        ctx.beginPath();
        ctx.moveTo(pad.left, y);
        ctx.lineTo(pad.left + chartW, y);
        ctx.stroke();
        ctx.fillStyle = '#9ca3af';
        ctx.font = '11px Segoe UI';
        ctx.textAlign = 'right';
        ctx.fillText(Math.round((gi / 4) * maxVal), pad.left - 6, y + 4);
    }

    // Line
    var step = labels.length > 1 ? chartW / (labels.length - 1) : chartW;
    ctx.beginPath();
    ctx.strokeStyle = '#2563eb';
    ctx.lineWidth   = 2.5;
    ctx.lineJoin    = 'round';
    values.forEach(function(v, i) {
        var x = pad.left + i * step;
        var y = pad.top + chartH - (v / maxVal) * chartH;
        if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
    });
    ctx.stroke();

    // Fill under line
    ctx.beginPath();
    values.forEach(function(v, i) {
        var x = pad.left + i * step;
        var y = pad.top + chartH - (v / maxVal) * chartH;
        if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
    });
    ctx.lineTo(pad.left + (values.length - 1) * step, pad.top + chartH);
    ctx.lineTo(pad.left, pad.top + chartH);
    ctx.closePath();
    ctx.fillStyle = 'rgba(37,99,235,0.09)';
    ctx.fill();

    // Dots + X labels
    ctx.fillStyle = '#2563eb';
    values.forEach(function(v, i) {
        var x = pad.left + i * step;
        var y = pad.top + chartH - (v / maxVal) * chartH;
        ctx.beginPath();
        ctx.arc(x, y, 4, 0, 2 * Math.PI);
        ctx.fill();
        // X label (every 2nd to avoid clutter)
        if (i % 2 === 0) {
            ctx.fillStyle = '#6b7280';
            ctx.font = '10px Segoe UI';
            ctx.textAlign = 'center';
            ctx.fillText(labels[i], x, H - 8);
            ctx.fillStyle = '#2563eb';
        }
    });
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/../footer.php'; ?>