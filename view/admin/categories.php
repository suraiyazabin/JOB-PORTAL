<?php
// $categories: array with id, name, description, job_count
// $editCat: category row when editing (or null)

$pageTitle  = 'Job Categories';
$activePage = 'categories';
include __DIR__ . '/../header.php';
?>

<div class="page-header">
    <h1>Job Categories</h1>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:22px;align-items:start">

<!-- LEFT: Category List -->
<div class="card">
    <h2>All Categories</h2>
    <?php if (empty($categories)): ?>
        <p class="text-muted">No categories yet. Add one using the form →</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Category Name</th><th>Description</th><th>Jobs</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                    <td><small><?php echo htmlspecialchars($cat['description'] ?? '—'); ?></small></td>
                    <td>
                        <span class="badge <?php echo $cat['job_count'] > 0 ? 'badge-shortlisted' : 'badge-draft'; ?>">
                            <?php echo $cat['job_count']; ?>
                        </span>
                    </td>
                    <td>
                        <a href="AdminCategoryController.php?action=edit&id=<?php echo $cat['id']; ?>"
                           class="btn btn-sm btn-info">Edit</a>

                        <form action="AdminCategoryController.php" method="post" style="display:inline"
                              onsubmit="return confirm('Delete category: <?php echo htmlspecialchars($cat['name']); ?>?')">
                            <input type="hidden" name="action"  value="delete">
                            <input type="hidden" name="cat_id"  value="<?php echo $cat['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger"
                                    <?php echo $cat['job_count'] > 0 ? 'title="Cannot delete: has active jobs" style=\"opacity:.5\"' : ''; ?>>
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- RIGHT: Add / Edit Form -->
<div class="card">
    <h2><?php echo $editCat ? 'Edit Category' : 'Add New Category'; ?></h2>
    <form action="AdminCategoryController.php" method="post">
        <input type="hidden" name="action"
               value="<?php echo $editCat ? 'edit_save' : 'add'; ?>">
        <?php if ($editCat): ?>
            <input type="hidden" name="cat_id" value="<?php echo $editCat['id']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Category Name *</label>
            <input type="text" name="name" required
                   value="<?php echo htmlspecialchars($editCat['name'] ?? ''); ?>"
                   placeholder="e.g. Software Engineering">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3"
                      placeholder="Brief description..."><?php echo htmlspecialchars($editCat['description'] ?? ''); ?></textarea>
        </div>

        <div class="flex" style="gap:10px">
            <button type="submit" class="btn btn-primary">
                <?php echo $editCat ? '💾 Update' : '+ Add Category'; ?>
            </button>
            <?php if ($editCat): ?>
                <a href="AdminCategoryController.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

</div>

<?php include __DIR__ . '/../footer.php'; ?>