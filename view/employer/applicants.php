<?php

$pageTitle  = 'Applicants';
$activePage = 'applicants';
include __DIR__ . '/../header.php';

$statusOptions = ['submitted', 'reviewed', 'shortlisted', 'interview', 'rejected'];
?>

<div class="page-header">
    <h1>
        <?php if ($job): ?>
            Applicants — <em><?php echo htmlspecialchars($job['title']); ?></em>
        <?php else: ?>
            Shortlisted &amp; Interview Candidates
        <?php endif; ?>
    </h1>
    <div class="flex" style="gap:10px">
        <a href="JobController.php" class="btn btn-secondary">← Jobs</a>
        <?php if ($job): ?>
            <a href="AnalyticsController.php?job_id=<?php echo $job['id']; ?>"
               class="btn btn-info">Analytics</a>
        <?php endif; ?>
    </div>
</div>

<!-- Filter bar (client-side JS filter) -->
<div class="card" style="padding:16px">
    <div class="flex" style="gap:12px;flex-wrap:wrap">
        <div>
            <label style="margin-bottom:4px">Filter by Status</label>
            <select id="filterStatus" onchange="filterTable()">
                <option value="">All Statuses</option>
                <?php foreach ($statusOptions as $s): ?>
                    <option value="<?php echo $s; ?>"><?php echo ucfirst($s); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="margin-bottom:4px">Search Name</label>
            <input type="text" id="filterName" oninput="filterTable()"
                   placeholder="Search by name...">
        </div>
        <div style="align-self:flex-end">
            <span id="resultCount" class="text-muted"></span>
        </div>
    </div>
</div>

<?php if (empty($applications)): ?>
    <div class="card text-center" style="padding:48px">
        <p class="text-muted" style="font-size:1.05rem">No applications found.</p>
    </div>

<?php else: ?>
<div class="card">
    <div class="table-wrap">
        <table id="appTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Applicant</th>
                    <th>Headline / Skills</th>
                    <th>Experience</th>
                    <th>Applied</th>
                    <th>Status</th>
                    <th>Resume</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $i = 1; foreach ($applications as $app): ?>
                <tr id="appRow_<?php echo $app['id']; ?>">
                    <td><?php echo $i++; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($app['seeker_name']); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($app['seeker_email']); ?></small><br>
                        <?php if ($app['seeker_phone']): ?>
                            <small class="text-muted">📞 <?php echo htmlspecialchars($app['seeker_phone']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($app['headline']): ?>
                            <strong><?php echo htmlspecialchars($app['headline']); ?></strong><br>
                        <?php endif; ?>
                        <?php if ($app['skills']): ?>
                            <small class="text-muted"><?php echo htmlspecialchars(substr($app['skills'], 0, 60)); ?>...</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo $app['years_experience'] ?? '0'; ?> yr(s)<br>
                        <small><?php echo htmlspecialchars($app['education_level'] ?? ''); ?></small>
                    </td>
                    <td>
                        <small><?php echo date('d M Y', strtotime($app['applied_at'])); ?></small>
                    </td>
                    <td>
                        <!-- Status badge + AJAX dropdown -->
                        <span class="badge badge-<?php echo $app['status']; ?> status-badge"
                              id="statusBadge_<?php echo $app['id']; ?>">
                            <?php echo ucfirst($app['status']); ?>
                        </span>
                        <br><br>
                        <?php if (!in_array($app['status'], ['rejected', 'withdrawn'])): ?>
                            <select onchange="updateAppStatus(this, <?php echo $app['id']; ?>)"
                                    style="font-size:.8rem;padding:3px 6px;border-radius:5px">
                                <option value="">Change status...</option>
                                <?php foreach (['reviewed', 'shortlisted', 'interview', 'rejected'] as $s): ?>
                                    <option value="<?php echo $s; ?>"
                                        <?php echo $app['status'] === $s ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($s); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $resumePath = $app['resume_path'] ?? ($app['profile_resume'] ?? '');
                        if ($resumePath):
                        ?>
                            <a href="../<?php echo htmlspecialchars($resumePath); ?>"
                               target="_blank" class="btn btn-sm btn-success">📄 Resume</a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="ApplicantController.php?action=view&id=<?php echo $app['id']; ?>"
                           class="btn btn-sm btn-primary">View Profile</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
function filterTable() {
    var status = document.getElementById('filterStatus').value.toLowerCase();
    var name   = document.getElementById('filterName').value.toLowerCase();
    var rows   = document.querySelectorAll('#appTable tbody tr');
    var count  = 0;
    rows.forEach(function(row) {
        var badge  = row.querySelector('.status-badge');
        var badgeText = badge ? badge.innerText.toLowerCase() : '';
        var nameText  = row.cells[1] ? row.cells[1].innerText.toLowerCase() : '';
        var showStatus = !status || badgeText.includes(status);
        var showName   = !name   || nameText.includes(name);
        if (showStatus && showName) {
            row.style.display = '';
            count++;
        } else {
            row.style.display = 'none';
        }
    });
    var el = document.getElementById('resultCount');
    if (el) el.innerHTML = 'Showing ' + count + ' of ' + rows.length;
}
// Init count on load
window.addEventListener('load', function() { filterTable(); });
</script>

<?php include __DIR__ . '/../footer.php'; ?>