<?php
// $complaints: array of complaint rows with submitter_name, subject_name etc.

$pageTitle  = 'Complaints & Disputes';
$activePage = 'complaints';
include __DIR__ . '/../header.php';

$open     = array_filter($complaints, fn($c) => $c['status'] === 'open');
$resolved = array_filter($complaints, fn($c) => $c['status'] === 'resolved');
?>

<div class="page-header">
    <h1>Complaints &amp; Disputes</h1>
    <div class="flex" style="gap:10px">
        <span class="badge badge-interview" style="padding:6px 14px;font-size:.9rem">
            <?php echo count($open); ?> Open
        </span>
        <span class="badge badge-shortlisted" style="padding:6px 14px;font-size:.9rem">
            <?php echo count($resolved); ?> Resolved
        </span>
    </div>
</div>

<!-- Open Complaints -->
<?php if (empty($open)): ?>
    <div class="card text-center" style="padding:36px">
        <p class="text-muted">✅ No open complaints. All resolved!</p>
    </div>
<?php else: ?>
    <h2 style="color:#1e3a5f;margin-bottom:12px">Open Complaints</h2>
    <?php foreach ($open as $c): ?>
    <div class="card" style="border-left:4px solid #ef4444">
        <div class="flex-between" style="margin-bottom:14px">
            <div>
                <strong>From:</strong> <?php echo htmlspecialchars($c['submitter_name']); ?>
                <small class="text-muted">&lt;<?php echo htmlspecialchars($c['submitter_email']); ?>&gt;</small>
                &nbsp;→&nbsp;
                <strong>Against:</strong> <?php echo htmlspecialchars($c['subject_name']); ?>
                <small class="text-muted">&lt;<?php echo htmlspecialchars($c['subject_email']); ?>&gt;</small>
            </div>
            <small class="text-muted"><?php echo date('d M Y', strtotime($c['created_at'])); ?></small>
        </div>

        <div style="background:#fff7f7;border-radius:6px;padding:12px 16px;margin-bottom:16px">
            <p><?php echo nl2br(htmlspecialchars($c['description'])); ?></p>
        </div>

        <form action="AdminComplaintController.php" method="post">
            <input type="hidden" name="action"       value="resolve">
            <input type="hidden" name="complaint_id" value="<?php echo $c['id']; ?>">
            <div class="flex" style="gap:10px;align-items:flex-end">
                <div class="form-group" style="flex:1;margin-bottom:0">
                    <label>Admin Resolution Note</label>
                    <input type="text" name="admin_note"
                           placeholder="Enter your resolution note (optional)...">
                </div>
                <button type="submit" class="btn btn-success"
                        onclick="return confirm('Mark as resolved?')">
                    ✓ Mark Resolved
                </button>
            </div>
        </form>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Resolved Complaints -->
<?php if (!empty($resolved)): ?>
<h2 style="color:#6b7280;margin:24px 0 12px">Resolved Complaints</h2>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>From</th><th>Against</th><th>Description</th><th>Admin Note</th><th>Date</th></tr>
            </thead>
            <tbody>
            <?php foreach ($resolved as $c): ?>
                <tr>
                    <td><?php echo htmlspecialchars($c['submitter_name']); ?></td>
                    <td><?php echo htmlspecialchars($c['subject_name']); ?></td>
                    <td><small><?php echo htmlspecialchars(substr($c['description'], 0, 70)); ?>...</small></td>
                    <td><small class="text-muted"><?php echo htmlspecialchars($c['admin_note'] ?? '—'); ?></small></td>
                    <td><small><?php echo date('d M Y', strtotime($c['created_at'])); ?></small></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../footer.php'; ?>