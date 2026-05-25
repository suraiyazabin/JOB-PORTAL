<?php
// $action: 'pipeline' or 'client_report'
// $pipeline, $analytics, $clients always set
// $client, $report set when action === 'client_report'

$pageTitle  = $action === 'client_report' ? 'Client Report' : 'Pipeline Management';
$activePage = 'pipeline';
include __DIR__ . '/../header.php';
?>

<?php if ($action === 'client_report'): ?>
<!-- ══ CLIENT REPORT ══════════════════════════════════════════ -->
<div class="page-header">
    <h1>Client Report — <?php echo htmlspecialchars($client['company_name_override'] ?: ($client['registered_company_name'] ?? 'Unknown')); ?></h1>
    <a href="PipelineController.php" class="btn btn-secondary">← Pipeline</a>
</div>

<?php if (empty($report)): ?>
    <div class="card text-center" style="padding:36px">
        <p class="text-muted">No jobs posted for this client yet.</p>
    </div>
<?php else: ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Status</th>
                    <th>Total Apps</th>
                    <th>Submitted</th>
                    <th>Reviewed</th>
                    <th>Shortlisted</th>
                    <th>Interview</th>
                    <th>Rejected</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($report as $row): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                    <td>
                        <span class="badge badge-<?php echo $row['status']; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td><strong><?php echo $row['total_apps']; ?></strong></td>
                    <td><?php echo $row['submitted']; ?></td>
                    <td><?php echo $row['reviewed']; ?></td>
                    <td><?php echo $row['shortlisted']; ?></td>
                    <td><?php echo $row['interview']; ?></td>
                    <td><?php echo $row['rejected']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<!-- ══ FULL PIPELINE ══════════════════════════════════════════ -->
<div class="page-header">
    <h1>Pipeline Management</h1>
    <div class="flex" style="gap:10px">
        <a href="CandidateController.php" class="btn btn-info">Search Candidates</a>
    </div>
</div>

<!-- Analytics Stats -->
<div class="stats-grid">
    <div class="stat-card info">
        <div class="stat-value"><?php echo $analytics['total_applications']; ?></div>
        <div class="stat-label">Total Managed</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $analytics['pipeline']['submitted'] ?? 0; ?></div>
        <div class="stat-label">Submitted</div>
    </div>
    <div class="stat-card success">
        <div class="stat-value"><?php echo $analytics['pipeline']['shortlisted'] ?? 0; ?></div>
        <div class="stat-label">Shortlisted</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-value"><?php echo $analytics['pipeline']['interview'] ?? 0; ?></div>
        <div class="stat-label">Interview</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-value"><?php echo $analytics['pipeline']['rejected'] ?? 0; ?></div>
        <div class="stat-label">Rejected</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $analytics['response_rate']; ?>%</div>
        <div class="stat-label">Outreach Response Rate</div>
    </div>
</div>

<!-- Filter + Client Reports -->
<div class="card" style="padding:16px">
    <div class="flex-between" style="flex-wrap:wrap;gap:12px">
        <div class="flex" style="gap:10px;align-items:center">
            <label style="margin:0;white-space:nowrap">Filter by Stage:</label>
            <?php
            $stages = ['' => 'All', 'submitted' => 'Submitted', 'reviewed' => 'Reviewed',
                       'shortlisted' => 'Shortlisted', 'interview' => 'Interview', 'rejected' => 'Rejected'];
            $curStatus = $_GET['status'] ?? '';
            foreach ($stages as $val => $lbl):
            ?>
                <a href="PipelineController.php<?php echo $val ? '?status=' . $val : ''; ?>"
                   class="btn btn-sm <?php echo $curStatus === $val ? 'btn-primary' : 'btn-outline'; ?>">
                    <?php echo $lbl; ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($clients)): ?>
        <div class="flex" style="gap:8px;align-items:center">
            <label style="margin:0;white-space:nowrap">Client Report:</label>
            <select onchange="if(this.value) window.location='PipelineController.php?action=client_report&client_id='+this.value">
                <option value="">— Select Client —</option>
                <?php foreach ($clients as $cl): ?>
                    <option value="<?php echo $cl['id']; ?>">
                        <?php echo htmlspecialchars($cl['company_name_override'] ?: ($cl['registered_company_name'] ?? 'Unknown')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($pipeline)): ?>
    <div class="card text-center" style="padding:48px">
        <p class="text-muted">No candidates in pipeline yet. Post jobs and receive applications.</p>
    </div>
<?php else: ?>

<!-- Kanban Board (by stage) -->
<?php
$stageList = [
    'submitted'   => ['label' => 'Submitted',   'color' => '#3b82f6'],
    'reviewed'    => ['label' => 'Reviewed',     'color' => '#6366f1'],
    'shortlisted' => ['label' => 'Shortlisted',  'color' => '#10b981'],
    'interview'   => ['label' => 'Interview',    'color' => '#f59e0b'],
    'rejected'    => ['label' => 'Rejected',     'color' => '#ef4444'],
];
$grouped = [];
foreach ($pipeline as $app) {
    $grouped[$app['status']][] = $app;
}
?>

<?php if (!$curStatus): ?>
<!-- Kanban view when no filter -->
<div class="pipeline-board" style="grid-template-columns:repeat(5,1fr)">
    <?php foreach ($stageList as $stage => $info): ?>
    <div class="pipeline-col">
        <div class="pipeline-col-header" style="border-bottom-color:<?php echo $info['color']; ?>">
            <span style="color:<?php echo $info['color']; ?>"><?php echo $info['label']; ?></span>
            <span style="background:#fff;padding:2px 8px;border-radius:12px;font-size:.8rem">
                <?php echo count($grouped[$stage] ?? []); ?>
            </span>
        </div>
        <?php foreach ($grouped[$stage] ?? [] as $app): ?>
            <div class="pipeline-card" id="pipeRow_<?php echo $app['id']; ?>">
                <strong><?php echo htmlspecialchars($app['seeker_name']); ?></strong>
                <small><?php echo htmlspecialchars($app['job_title']); ?></small><br>
                <?php if ($app['client_name']): ?>
                    <small class="text-muted"><?php echo htmlspecialchars($app['client_name']); ?></small><br>
                <?php endif; ?>
                <?php if ($stage !== 'rejected'): ?>
                <select onchange="updatePipelineStatus(this, <?php echo $app['id']; ?>)"
                        style="margin-top:6px;font-size:.78rem;width:100%;padding:3px;border-radius:5px;border:1px solid #d1d5db">
                    <option value="">Move to...</option>
                    <?php foreach (['reviewed','shortlisted','interview','rejected'] as $s): ?>
                        <?php if ($s !== $stage): ?>
                        <option value="<?php echo $s; ?>"><?php echo ucfirst($s); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                <span class="badge badge-<?php echo $stage; ?> status-badge" style="margin-top:4px;font-size:.72rem">
                    <?php echo ucfirst($stage); ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>

<?php else: ?>
<!-- Table view when filtered by stage -->
<div class="card">
    <h2><?php echo $stageList[$curStatus]['label']; ?> Candidates</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Skills</th>
                    <th>Job</th>
                    <th>Client</th>
                    <th>Applied</th>
                    <th>Stage</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pipeline as $app): ?>
                <tr id="pipeRow_<?php echo $app['id']; ?>">
                    <td>
                        <strong><?php echo htmlspecialchars($app['seeker_name']); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($app['seeker_email']); ?></small>
                    </td>
                    <td><small><?php echo htmlspecialchars(substr($app['skills'] ?? '—', 0, 50)); ?></small></td>
                    <td><?php echo htmlspecialchars($app['job_title']); ?></td>
                    <td><?php echo htmlspecialchars($app['client_name'] ?? '—'); ?></td>
                    <td><small><?php echo date('d M Y', strtotime($app['applied_at'])); ?></small></td>
                    <td>
                        <span class="badge badge-<?php echo $app['status']; ?> status-badge">
                            <?php echo ucfirst($app['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($app['status'] !== 'rejected'): ?>
                        <select onchange="updatePipelineStatus(this, <?php echo $app['id']; ?>)"
                                style="font-size:.82rem;padding:4px 6px;border-radius:5px">
                            <option value="">Change...</option>
                            <?php foreach (['reviewed','shortlisted','interview','rejected'] as $s): ?>
                                <option value="<?php echo $s; ?>"
                                    <?php echo $app['status']===$s?'selected':''; ?>>
                                    <?php echo ucfirst($s); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php endif; // empty pipeline ?>
<?php endif; // action ?>

<?php include __DIR__ . '/../footer.php'; ?>