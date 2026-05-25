<?php
$pageTitle  = 'Admin Dashboard';
$activePage = 'dashboard';
include __DIR__ . '/../header.php';

// Simple reusable SVG icons (no dependencies)
function icon($type) {
    switch ($type) {
        case 'dashboard':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 13h8V3H3v10zm10 8h8V11h-8v10zM3 21h8v-6H3v6zm10-18v6h8V3h-8z"/></svg>';

        case 'warning':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01M10.29 3.86l-8.2 14A2 2 0 0 0 3.8 21h16.4a2 2 0 0 0 1.71-3.14l-8.2-14a2 2 0 0 0-3.42 0z"/></svg>';

        case 'building':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M6 21V7l6-4 6 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01"/></svg>';

        case 'target':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>';

        case 'folder':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h5l2 2h11v10a2 2 0 0 1-2 2H3z"/></svg>';

        case 'shield':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l8 4v6c0 5-3.5 9.5-8 10-4.5-.5-8-5-8-10V6l8-4z"/></svg>';
    }
}
?>

<div class="page-header">
    <h1><?php echo icon('shield'); ?> Admin Dashboard</h1>

    <a href="AdminUserController.php?tab=pending" class="btn btn-warning">
        <?php echo icon('warning'); ?> Pending Approvals (<?php echo count($pending); ?>)
    </a>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo $stats['users']['seeker']    ?? 0; ?></div>
        <div class="stat-label">Job Seekers</div>
    </div>
    <div class="stat-card info">
        <div class="stat-value"><?php echo $stats['users']['employer']  ?? 0; ?></div>
        <div class="stat-label">Employers</div>
    </div>
    <div class="stat-card success">
        <div class="stat-value"><?php echo $stats['users']['recruiter'] ?? 0; ?></div>
        <div class="stat-label">Recruiters</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-value"><?php echo $stats['pending']; ?></div>
        <div class="stat-label">Pending Verification</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $stats['active_jobs']; ?></div>
        <div class="stat-label">Active Jobs</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-value"><?php echo $stats['open_complaints']; ?></div>
        <div class="stat-label">Open Complaints</div>
    </div>
    <div class="stat-card info">
        <div class="stat-value"><?php echo $stats['apps_today']; ?></div>
        <div class="stat-label">Applications Today</div>
    </div>
</div>

<!-- Pending Verifications -->
<div class="card">
    <h2>Pending Account Verifications</h2>

    <?php if (empty($pending)): ?>
        <p class="text-muted">✅ No pending verifications. All caught up!</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Name</th><th>Email</th><th>Role</th><th>Company / Agency</th><th>Registered</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($pending as $u): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $u['role'] === 'employer' ? 'submitted' : 'reviewed'; ?>">
                            <?php echo ucfirst($u['role']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($u['org_name'] ?? '—'); ?></td>
                    <td><small><?php echo date('d M Y', strtotime($u['created_at'])); ?></small></td>
                    <td>
                        <form action="AdminUserController.php" method="post" style="display:inline">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-success">✓ Approve</button>
                        </form>

                        <form action="AdminUserController.php" method="post" style="display:inline"
                              onsubmit="return confirm('Reject this account?')">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">✗ Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Quick Nav -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px">

<?php foreach ([
    ['AdminUserController.php?tab=employers',  icon('building'), 'Employers',  'Manage employer accounts'],
    ['AdminUserController.php?tab=recruiters', icon('target'),   'Recruiters', 'Manage recruiter accounts'],
    ['AdminCategoryController.php',            icon('folder'),   'Categories', 'Add, rename, delete categories'],
    ['AdminComplaintController.php',           icon('warning'),  'Complaints',  $stats['open_complaints'] . ' open'],
] as [$url, $icon, $label, $desc]): ?>

    <a href="<?php echo $url; ?>" style="text-decoration:none">
        <div class="card" style="text-align:center;padding:20px;cursor:pointer;transition:box-shadow .2s"
             onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.15)'"
             onmouseout="this.style.boxShadow=''">

            <div style="font-size:2rem;margin-bottom:8px">
                <?php echo $icon; ?>
            </div>

            <strong><?php echo $label; ?></strong>
            <p class="text-muted" style="font-size:.82rem;margin-top:4px">
                <?php echo $desc; ?>
            </p>
        </div>
    </a>

<?php endforeach; ?>

</div>

<?php include __DIR__ . '/../footer.php'; ?>