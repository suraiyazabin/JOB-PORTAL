<div class="page-header"><h1>Job Categories</h1></div>
<?php if ($msg):   ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="two-col">
  <div class="card">
    <h2>Add New Category</h2>
    <form method="POST" action="AdminController.php?action=categories">
      <input type="hidden" name="submit_action" value="add_category">
      <div class="form-group"><label>Category Name *</label><input type="text" name="name" required placeholder="e.g. Technology"></div>
      <div class="form-group"><label>Description</label><textarea name="description" rows="3" placeholder="Brief description..."></textarea></div>
      <button type="submit" class="btn btn-primary">Add Category</button>
    </form>
  </div>
  <div class="card">
    <h2>All Categories (<?= count($categories) ?>)</h2>
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Name</th><th>Active Jobs</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($categories as $i => $c): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td>
              <strong><?= htmlspecialchars($c['name']) ?></strong>
              <?php if ($c['description']): ?><br><small style="color:#5a7a9a;"><?= htmlspecialchars($c['description']) ?></small><?php endif; ?>
            </td>
            <td><span class="badge badge-secondary"><?= $c['job_count'] ?> jobs</span></td>
            <td style="white-space:nowrap;">
              <button class="btn btn-primary btn-sm" onclick="openEdit(<?= $c['id'] ?>, '<?= addslashes(htmlspecialchars($c['name'])) ?>', '<?= addslashes(htmlspecialchars($c['description']??'')) ?>')">Edit</button>
              <?php if ($c['job_count'] == 0): ?>
              <form method="POST" action="AdminController.php?action=categories" style="display:inline;" onsubmit="return confirm('Delete this category?')">
                <input type="hidden" name="submit_action" value="delete_category">
                <input type="hidden" name="cat_id" value="<?= $c['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($categories)): ?><tr><td colspan="4" class="no-data">No categories yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;display:flex;align-items:center;justify-content:center;" class="hidden">
  <div style="background:#fff;border-radius:4px;padding:30px;width:440px;max-width:95%;">
    <h3 style="color:#0d2a4e;margin-bottom:16px;">Edit Category</h3>
    <form method="POST" action="AdminController.php?action=categories">
      <input type="hidden" name="submit_action" value="edit_category">
      <input type="hidden" name="cat_id" id="edit-id">
      <div class="form-group"><label>Name *</label><input type="text" name="name" id="edit-name" required></div>
      <div class="form-group"><label>Description</label><textarea name="description" id="edit-desc" rows="3"></textarea></div>
      <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn btn-secondary" onclick="closeEdit()">Cancel</button>
      </div>
    </form>
  </div>
</div>
<script>
function openEdit(id, name, desc) {
  document.getElementById('edit-id').value   = id;
  document.getElementById('edit-name').value = name;
  document.getElementById('edit-desc').value = desc;
  document.getElementById('edit-modal').style.display = 'flex';
}
function closeEdit() { document.getElementById('edit-modal').style.display = 'none'; }
</script>