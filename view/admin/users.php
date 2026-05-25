<?php
// $tab: 'pending' | 'employers' | 'recruiters' | 'seekers'
// $users: array of user rows
// $search: search string (seekers tab)

$pageTitle  = 'User Management';
$activePage = 'users';
include __DIR__ . '/../header.php';
?>

<div class="page-header">
    <h1>User Management</h1>
</div>

<!-- Tab Nav -->
<div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid #e5e7eb;padding-bottom:0">
    <?php foreach ([
        'pending'   => '⏳ Pending (' . (($tab==='pending') ? count($users) : '?') . ')',
        'employers' => '🏢 Employers',
        'recruiters'=> '🎯 Recruiters',
        'seekers'   => '👤 Seekers',
    ] as $t => $label): ?>
        <a href="AdminUserController.php?tab=<?php echo $t; ?>"
           style="padding:10px 18px;border-radius:6px 6px 0 0;text-decoration:none;font-weight:600;font-size:.9rem;
                  background:<?php echo $tab===$t?'#1e3a5f':'#f3f4f6'; ?>;
                  color:<?php echo $tab===$t?'#fff':'#4b5563'; ?>;
                  border:2px solid <?php echo $tab===$t?'#1e3a5f':'#e5e7eb'; ?>;
                  border-bottom:none;margin-bottom:-2px">
            <?php echo $label; ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Seeker Search -->
<?php if ($tab === 'seekers'): ?>
<form action="AdminUserController.php" method="get" style="margin-bottom:16px">
    <input type="hidden" name="tab" value="seekers">
    <div class="flex" style="gap:10px">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>"
               placeholder="Search by name or email..." style="max-width:340px">
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if (!empty($search)): ?>
            <a href="AdminUserController.php?tab=seekers" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </div>
</form>
<?php endif; ?>

<?php if (empty($users)): ?>
    <div class="card text-center" style="padding:40px">
        <p class="text-muted">No users found in this category.</p>
    </div>
<?php else: ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <?php if ($tab !== 'seekers'): ?><th>Company / Agency</th><?php endif; ?>
                    <th>Phone</th>
                    <th>Registered</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <?php if ($tab !== 'seekers'): ?>
                        <td><?php echo htmlspecialchars($u['org_name'] ?? '—'); ?></td>
                    <?php endif; ?>
                    <td><?php echo htmlspecialchars($u['phone'] ?? '—'); ?></td>
                    <td><small><?php echo date('d M Y', strtotime($u['created_at'])); ?></small></td>
                    <td>
                        <?php if ($tab === 'pending'): ?>
                            <span class="badge badge-interview">Pending</span>
                        <?php elseif (!empty($u['is_verified']) && $u['is_verified']): ?>
                            <span class="badge badge-shortlisted">Verified</span>
                        <?php else: ?>
                            <span class="badge badge-rejected">Not Verified</span>
                        <?php endif; ?>
                        <?php if (isset($u['is_active']) && !$u['is_active']): ?>
                            <span class="badge badge-rejected">Suspended</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($tab === 'pending'): ?>
                            <form action="AdminUserController.php" method="post" style="display:inline">
                                <input type="hidden" name="action"  value="approve">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-success">✓ Approve</button>
                            </form>
                            <form action="AdminUserController.php" method="post" style="display:inline"
                                  onsubmit="return confirm('Reject this account?')">
                                <input type="hidden" name="action"  value="reject">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">✗ Reject</button>
                            </form>
                        <?php else: ?>
                            <?php $isActive = $u['is_active'] ?? 1; ?>
                            <form action="AdminUserController.php" method="post" style="display:inline"
                                  onsubmit="return confirm('<?php echo $isActive ? 'Suspend' : 'Reactivate'; ?> this account?')">
                                <input type="hidden" name="action"    value="toggle_active">
                                <input type="hidden" name="user_id"   value="<?php echo $u['id']; ?>">
                                <input type="hidden" name="is_active" value="<?php echo $isActive ? '0' : '1'; ?>">
                                <input type="hidden" name="tab"       value="<?php echo $tab; ?>">
                                <button type="submit" class="btn btn-sm btn-<?php echo $isActive ? 'warning' : 'success'; ?>">
                                    <?php echo $isActive ? 'Suspend' : 'Reactivate'; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="text-muted" style="margin-top:12px;font-size:.85rem">
        Total: <?php echo count($users); ?> user(s)
    </p>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../footer.php'; ?>