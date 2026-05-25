<?php
// Variables from AdminReportsController:
// $stats, $jobsByCategory, $registrations, $topEmployers, $activeRecruiters, $popularLocations

$pageTitle  = 'Platform Reports';
$activePage = 'reports';
include __DIR__ . '/../header.php';

// Build registration chart data grouped by month
$regByMonth = [];
foreach ($registrations as $row) {
    $m = $row['month'];
    if (!isset($regByMonth[$m])) {
        $regByMonth[$m] = ['seeker' => 0, 'employer' => 0, 'recruiter' => 0];
    }
    $regByMonth[$m][$row['role']] = (int)$row['cnt'];
}
$regMonths  = array_keys($regByMonth);
$regSeekers = array_map(fn($m) => $regByMonth[$m]['seeker']   ?? 0, $regMonths);
$regEmployers=array_map(fn($m) => $regByMonth[$m]['employer'] ?? 0, $regMonths);
$regRecruiters=array_map(fn($m)=> $regByMonth[$m]['recruiter']?? 0, $regMonths);
?>

<div class="page-header">
    <h1>Platform Reports &amp; Analytics</h1>
    <a href="AdminDashboardController.php" class="btn btn-secondary">← Dashboard</a>
</div>

<!-- Platform Summary Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo array_sum(array_column($stats['users'], null) ?: $stats['users']); ?></div>
        <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-card success">
        <div class="stat-value"><?php echo $stats['active_jobs']; ?></div>
        <div class="stat-label">Active Jobs</div>
    </div>
    <div class="stat-card info">
        <div class="stat-value"><?php echo $stats['users']['seeker'] ?? 0; ?></div>
        <div class="stat-label">Seekers</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-value"><?php echo $stats['users']['employer'] ?? 0; ?></div>
        <div class="stat-label">Employers</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $stats['users']['recruiter'] ?? 0; ?></div>
        <div class="stat-label">Recruiters</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-value"><?php echo $stats['open_complaints']; ?></div>
        <div class="stat-label">Open Complaints</div>
    </div>
</div>

<!-- Jobs by Category -->
<div class="card">
    <h2>Jobs by Category</h2>
    <?php if (empty($jobsByCategory)): ?>
        <p class="text-muted">No job data yet.</p>
    <?php else: ?>
    <?php $maxJobs = max(1, max(array_column($jobsByCategory, 'total'))); ?>
    <?php foreach ($jobsByCategory as $row): ?>
        <div class="funnel-row">
            <div class="funnel-label"><?php echo htmlspecialchars($row['name']); ?></div>
            <div class="funnel-bar-wrap">
                <div class="funnel-bar submitted"
                     style="width:<?php echo round(($row['total']/$maxJobs)*100); ?>%;background:#2563eb">
                    <?php echo $row['total']; ?> total
                </div>
            </div>
            <div style="width:70px;font-size:.82rem;color:#6b7280">
                <?php echo $row['active']; ?> active
            </div>
        </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- User Growth Chart (last 6 months) -->
<?php if (!empty($regMonths)): ?>
<div class="card">
    <h2>New Registrations (Last 6 Months)</h2>
    <canvas id="regChart" style="max-height:220px;width:100%"></canvas>
    <div class="flex" style="gap:18px;margin-top:10px;font-size:.85rem">
        <span style="color:#3b82f6">■ Seekers</span>
        <span style="color:#10b981">■ Employers</span>
        <span style="color:#f59e0b">■ Recruiters</span>
    </div>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:22px">

<!-- Top Employers -->
<div class="card">
    <h2>Top Employers by Applications</h2>
    <?php if (empty($topEmployers)): ?>
        <p class="text-muted">No data yet.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Company</th><th>Jobs</th><th>Applications</th></tr></thead>
            <tbody>
            <?php foreach ($topEmployers as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                    <td><?php echo $row['job_count']; ?></td>
                    <td><strong><?php echo $row['app_count']; ?></strong></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Most Active Recruiters -->
<div class="card">
    <h2>Most Active Recruiters</h2>
    <?php if (empty($activeRecruiters)): ?>
        <p class="text-muted">No data yet.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Agency</th><th>Jobs Posted</th><th>Outreach Sent</th></tr></thead>
            <tbody>
            <?php foreach ($activeRecruiters as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['agency_name']); ?></td>
                    <td><?php echo $row['jobs_posted']; ?></td>
                    <td><?php echo $row['outreach_sent']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

</div>

<!-- Popular Locations -->
<?php if (!empty($popularLocations)): ?>
<div class="card">
    <h2>Popular Job Locations</h2>
    <div class="flex" style="flex-wrap:wrap;gap:10px">
        <?php
        $maxLoc = max(1, max(array_column($popularLocations, 'cnt')));
        foreach ($popularLocations as $i => $loc):
            $size = 0.85 + ($loc['cnt'] / $maxLoc) * 0.6;
        ?>
            <span style="font-size:<?php echo round($size, 2); ?>rem;
                         background:#dbeafe;color:#1e40af;
                         padding:5px 14px;border-radius:20px">
                <?php echo htmlspecialchars($loc['location']); ?>
                <small>(<?php echo $loc['cnt']; ?>)</small>
            </span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Registration Chart Script -->
<?php if (!empty($regMonths)): ?>
<script>
(function() {
    var labels     = <?php echo json_encode(array_map(fn($m) => date('M Y', strtotime($m.'-01')), $regMonths)); ?>;
    var seekers    = <?php echo json_encode($regSeekers); ?>;
    var employers  = <?php echo json_encode($regEmployers); ?>;
    var recruiters = <?php echo json_encode($regRecruiters); ?>;
    var canvas = document.getElementById('regChart');
    if (!canvas) return;
    var W = canvas.offsetWidth || 700, H = 200;
    canvas.width = W; canvas.height = H;
    var ctx = canvas.getContext('2d');
    var pad = {top:16,right:16,bottom:32,left:40};
    var cW = W-pad.left-pad.right, cH = H-pad.top-pad.bottom;
    var allVals = seekers.concat(employers, recruiters);
    var maxV = Math.max.apply(null, allVals) || 1;
    var step = labels.length>1 ? cW/(labels.length-1) : cW;

    ctx.fillStyle='#f8fafc'; ctx.fillRect(0,0,W,H);

    // Grid
    for(var gi=0;gi<=4;gi++){
        var gy=pad.top+cH-(gi/4)*cH;
        ctx.strokeStyle='#e5e7eb'; ctx.lineWidth=1;
        ctx.beginPath(); ctx.moveTo(pad.left,gy); ctx.lineTo(pad.left+cW,gy); ctx.stroke();
        ctx.fillStyle='#9ca3af'; ctx.font='10px Segoe UI'; ctx.textAlign='right';
        ctx.fillText(Math.round((gi/4)*maxV), pad.left-4, gy+4);
    }

    // Draw a line series
    function drawLine(data, color) {
        ctx.beginPath(); ctx.strokeStyle=color; ctx.lineWidth=2; ctx.lineJoin='round';
        data.forEach(function(v,i){
            var x=pad.left+i*step, y=pad.top+cH-(v/maxV)*cH;
            if(i===0) ctx.moveTo(x,y); else ctx.lineTo(x,y);
        });
        ctx.stroke();
        data.forEach(function(v,i){
            ctx.fillStyle=color;
            ctx.beginPath(); ctx.arc(pad.left+i*step, pad.top+cH-(v/maxV)*cH, 3,0,2*Math.PI); ctx.fill();
        });
    }

    drawLine(seekers,    '#3b82f6');
    drawLine(employers,  '#10b981');
    drawLine(recruiters, '#f59e0b');

    // X labels
    ctx.fillStyle='#6b7280'; ctx.font='10px Segoe UI'; ctx.textAlign='center';
    labels.forEach(function(lbl,i){
        ctx.fillText(lbl, pad.left+i*step, H-8);
    });
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/../footer.php'; ?>