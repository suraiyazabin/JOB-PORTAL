<?php

$pageTitle  = 'Client Companies';
$activePage = 'clients';
include __DIR__ . '/../header.php';
?>

<?php if ($action === 'add'): ?>
<!-- ══ ADD CLIENT FORM ════════════════════════════════════════ -->

<div class="page-header">
    <h1>Add Client Company</h1>
    <a href="ClientController.php" class="btn btn-secondary">← Back to Clients</a>
</div>

<div class="card" style="max-width:620px">
    <p class="text-muted" style="margin-bottom:20px">
        Link a registered employer OR enter a standalone company name
        (for employers not yet registered on the platform).
    </p>

    <form action="ClientController.php" method="post">
        <input type="hidden" name="action" value="add_save">

        <div class="form-group">
            <label>Select Registered Employer <small>(optional)</small></label>
            <select name="employer_id">
                <option value="">— Not a registered user —</option>
                <?php foreach ($employers as $emp): ?>
                    <option value="<?php echo $emp['id']; ?>">
                        <?php echo htmlspecialchars($emp['company_name']); ?>
                        <?php if ($emp['industry']): ?>
                            (<?php echo htmlspecialchars($emp['industry']); ?>)
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small>If the company is on the platform, select them above to link properly.</small>
        </div>

        <div class="form-group">
            <label>Custom Company Name <small>(if not registered)</small></label>
            <input type="text" name="company_name_override"
                   placeholder="e.g. XYZ Corporation (External)">
            <small>Enter a name here if the employer is not registered. Leave blank if you selected above.</small>
        </div>

        <button type="submit" class="btn btn-primary">Add Client</button>
    </form>
</div>

<?php else: ?>
<!-- ══ CLIENTS LIST ═══════════════════════════════════════════ -->

<div class="page-header">
    <h1>Client Companies</h1>
    <a href="ClientController.php?action=add" class="btn btn-primary">+ Add Client</a>
</div>

<?php if (empty($clients)): ?>
    <div class="card text-center" style="padding:48px">
        <p class="text-muted" style="font-size:1.05rem;margin-bottom:16px">
            You haven't added any clients yet.
        </p>
        <a href="ClientController.php?action=add" class="btn btn-primary">Add Your First Client</a>
    </div>

<?php else: ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>Industry</th>
                    <th>Type</th>
                    <th>Jobs Posted</th>
                    <th>Added On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td>
                        <?php if (!empty($client['logo_path'])): ?>
                            <img src="../<?php echo htmlspecialchars($client['logo_path']); ?>"
                                 style="height:30px;border-radius:4px;margin-right:8px;vertical-align:middle">
                        <?php endif; ?>
                        <strong>
                            <?php echo htmlspecialchars(
                                $client['company_name_override'] ?: ($client['registered_company_name'] ?? 'Unknown')
                            ); ?>
                        </strong>
                    </td>
                    <td><?php echo htmlspecialchars($client['industry'] ?? '—'); ?></td>
                    <td>
                        <?php if ($client['employer_id']): ?>
                            <span class="badge badge-shortlisted">Registered</span>
                        <?php else: ?>
                            <span class="badge badge-draft">External</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="RecruiterJobController.php?client_id=<?php echo $client['id']; ?>">
                            <?php echo $client['job_count'] ?? 0; ?> job(s)
                        </a>
                    </td>
                    <td>
                        <small><?php echo date('d M Y', strtotime($client['added_at'])); ?></small>
                    </td>
                    <td>
                        <a href="RecruiterJobController.php?action=create&client_id=<?php echo $client['id']; ?>"
                           class="btn btn-sm btn-primary">Post Job</a>

                        <form action="ClientController.php" method="post" style="display:inline"
                              onsubmit="return confirm('Remove this client? Their jobs will remain.')">
                            <input type="hidden" name="action"    value="delete">
                            <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<?php include __DIR__ . '/../footer.php'; ?>