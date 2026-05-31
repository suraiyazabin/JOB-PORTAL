<div class="page-header"><h1>Messages</h1></div>
<?php if ($msg):   ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<?php if (empty($messages)): ?>
  <div class="card"><p class="no-data">No messages yet. Messages from employers and recruiters will appear here.</p></div>
<?php else: ?>
  <?php foreach ($messages as $m): ?>
  <div class="card" style="margin-bottom:14px;<?= !$m['is_read'] ? 'border-left:4px solid #c8a84b;' : '' ?>">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
      <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:40px;height:40px;border-radius:50%;background:#0d2a4e;display:flex;align-items:center;justify-content:center;color:#c8a84b;font-weight:bold;">
          <?= strtoupper(substr($m['sender_name'],0,1)) ?>
        </div>
        <div>
          <strong><?= htmlspecialchars($m['sender_name']) ?></strong>
          <span class="badge badge-secondary" style="margin-left:6px;"><?= $m['sender_role'] ?></span>
          <?php if (!$m['is_read']): ?><span class="badge badge-open" style="margin-left:6px;">New</span><?php endif; ?>
        </div>
      </div>
      <span style="color:#999;font-size:.8rem;"><?= date('d M Y, H:i', strtotime($m['sent_at'])) ?></span>
    </div>
    <p style="margin:12px 0;color:#2c3e50;"><?= nl2br(htmlspecialchars($m['body'])) ?></p>
    <div id="reply-box-<?= $m['id'] ?>" style="display:none;margin-top:10px;">
      <form method="POST" action="SeekerController.php?action=messages">
        <input type="hidden" name="submit_action" value="send_message">
        <input type="hidden" name="recipient_id" value="<?= $m['sender_id'] ?>">
        <input type="hidden" name="app_id" value="<?= $m['application_id'] ?? '' ?>">
        <div class="form-group"><textarea name="body" rows="3" placeholder="Write your reply..."></textarea></div>
        <button type="submit" class="btn btn-primary btn-sm">Send Reply</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('reply-box-<?= $m['id'] ?>').style.display='none'">Cancel</button>
      </form>
    </div>
    <button class="btn btn-secondary btn-sm" onclick="document.getElementById('reply-box-<?= $m['id'] ?>').style.display='block';this.style.display='none'">Reply</button>
  </div>
  <?php endforeach; ?>
<?php endif; ?>