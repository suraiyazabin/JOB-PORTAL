<?php

$pageTitle  = 'Applicant Profile';
$activePage = 'applicants';
include __DIR__ . '/../header.php';
?>

<div class="page-header">
    <h1>Applicant Profile</h1>
    <div class="flex" style="gap:10px">
        <a href="ApplicantController.php?job_id=<?php echo $app['job_id']; ?>"
           class="btn btn-secondary">← Back to Applicants</a>
        <a href="JobController.php" class="btn btn-secondary">Jobs</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:22px">

<!-- LEFT: Applicant detail -->
<div>

    <!-- Profile Card -->
    <div class="card">
        <div class="flex" style="gap:18px;align-items:flex-start;margin-bottom:18px">
            <?php if (!empty($app['profile_pic'])): ?>
                <img src="../<?php echo htmlspecialchars($app['profile_pic']); ?>"
                     style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid #e5e7eb">
            <?php else: ?>
                <div style="width:72px;height:72px;border-radius:50%;background:#1e3a5f;display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:#fff">
                    <?php echo strtoupper(substr($app['seeker_name'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            <div>
                <h2 style="font-size:1.4rem;color:#1e3a5f;margin-bottom:4px">
                    <?php echo htmlspecialchars($app['seeker_name']); ?>
                </h2>
                <?php if ($app['headline']): ?>
                    <p style="color:#4b5563;font-size:.95rem"><?php echo htmlspecialchars($app['headline']); ?></p>
                <?php endif; ?>
                <div class="flex" style="gap:12px;margin-top:6px;font-size:.85rem;color:#6b7280">
                    <span>📧 <?php echo htmlspecialchars($app['seeker_email']); ?></span>
                    <?php if ($app['seeker_phone']): ?>
                        <span>📞 <?php echo htmlspecialchars($app['seeker_phone']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="margin-left:auto">
                <span class="badge badge-<?php echo $app['status']; ?> status-badge"
                      id="statusBadge_<?php echo $app['id']; ?>"
                      style="font-size:.9rem;padding:6px 14px">
                    <?php echo ucfirst($app['status']); ?>
                </span>
            </div>
        </div>

        <!-- Applied for -->
        <div style="background:#f0f4ff;border-radius:8px;padding:12px 16px;margin-bottom:18px;font-size:.9rem">
            <strong>Applied for:</strong> <?php echo htmlspecialchars($app['job_title']); ?> &nbsp;|&nbsp;
            <strong>Date:</strong> <?php echo date('d M Y, g:i A', strtotime($app['applied_at'])); ?>
        </div>

        <!-- Professional Summary -->
        <?php if ($app['summary']): ?>
        <div class="form-group">
            <label style="color:#6b7280;font-size:.8rem;text-transform:uppercase;letter-spacing:.5px">Professional Summary</label>
            <p><?php echo nl2br(htmlspecialchars($app['summary'])); ?></p>
        </div>
        <?php endif; ?>

        <!-- Skills -->
        <?php if ($app['skills']): ?>
        <div class="form-group">
            <label style="color:#6b7280;font-size:.8rem;text-transform:uppercase;letter-spacing:.5px">Skills</label>
            <div class="flex" style="flex-wrap:wrap;gap:6px">
                <?php foreach (explode(',', $app['skills']) as $skill): ?>
                    <span style="background:#dbeafe;color:#1e40af;padding:3px 10px;border-radius:20px;font-size:.83rem">
                        <?php echo htmlspecialchars(trim($skill)); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Experience & Education -->
        <div class="form-row" style="margin-top:12px">
            <div>
                <label style="color:#6b7280;font-size:.8rem;text-transform:uppercase;letter-spacing:.5px">Experience</label>
                <p><?php echo $app['years_experience'] ?? '0'; ?> year(s)</p>
            </div>
            <div>
                <label style="color:#6b7280;font-size:.8rem;text-transform:uppercase;letter-spacing:.5px">Education</label>
                <p><?php echo htmlspecialchars($app['education_level'] ?? '—'); ?></p>
            </div>
        </div>
    </div>

    <!-- Cover Letter -->
    <?php if ($app['cover_letter']): ?>
    <div class="card">
        <h2>Cover Letter</h2>
        <p style="white-space:pre-wrap;line-height:1.8"><?php echo htmlspecialchars($app['cover_letter']); ?></p>
    </div>
    <?php endif; ?>

    <!-- Resume -->
    <div class="card">
        <h2>Resume</h2>
        <?php
        $resumePath = $app['resume_path'] ?? ($app['profile_resume'] ?? '');
        if ($resumePath):
        ?>
            <p class="text-muted" style="margin-bottom:12px">Submitted resume is available below.</p>
            <a href="../<?php echo htmlspecialchars($resumePath); ?>"
               target="_blank" class="btn btn-success">📄 Download / View Resume</a>
        <?php else: ?>
            <p class="text-muted">No resume attached to this application.</p>
        <?php endif; ?>
    </div>

</div><!-- /LEFT -->

<!-- RIGHT: Actions Panel -->
<div>

    <!-- Update Status -->
    <?php if (!in_array($app['status'], ['rejected', 'withdrawn'])): ?>
    <div class="card">
        <h2>Update Application Stage</h2>
        <div style="display:flex;flex-direction:column;gap:10px">
            <?php foreach (['reviewed' => ['info','🔍 Mark Reviewed'], 'shortlisted' => ['success','⭐ Shortlist'], 'interview' => ['warning','📅 Invite to Interview'], 'rejected' => ['danger','✗ Reject']] as $s => [$btnClass, $btnLabel]): ?>
                <?php if ($app['status'] !== $s): ?>
                    <button class="btn btn-<?php echo $btnClass; ?>"
                            onclick="quickStatusUpdate(<?php echo $app['id']; ?>, '<?php echo $s; ?>')">
                        <?php echo $btnLabel; ?>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Send Message -->
    <div class="card">
        <h2>Send Message</h2>
        <form action="ApplicantController.php" method="post">
            <input type="hidden" name="action"       value="send_message">
            <input type="hidden" name="app_id"       value="<?php echo $app['id']; ?>">
            <input type="hidden" name="recipient_id" value="<?php echo $app['seeker_id']; ?>">
            <div class="form-group">
                <textarea name="body" rows="4"
                          placeholder="Type your message (e.g. interview invitation, feedback...)"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Send Message</button>
        </form>
    </div>

    <!-- Submit Complaint -->
    <div class="card">
        <h2>Submit Complaint</h2>
        <p class="text-muted" style="font-size:.85rem;margin-bottom:12px">
            Report this applicant to platform admin for misconduct.
        </p>
        <form action="ApplicantController.php" method="post">
            <input type="hidden" name="action"     value="submit_complaint">
            <input type="hidden" name="subject_id" value="<?php echo $app['seeker_id']; ?>">
            <input type="hidden" name="app_id"     value="<?php echo $app['id']; ?>">
            <input type="hidden" name="job_id"     value="<?php echo $app['job_id']; ?>">
            <div class="form-group">
                <textarea name="description" rows="3"
                          placeholder="Describe the issue..."></textarea>
            </div>
            <button type="submit" class="btn btn-danger" style="width:100%"
                    onclick="return confirm('Submit this complaint to admin?')">
                Report to Admin
            </button>
        </form>
    </div>

</div><!-- /RIGHT -->
</div><!-- /grid -->

<script>
function quickStatusUpdate(appId, status) {
    var labels = {
        reviewed:'Reviewed', shortlisted:'Shortlisted',
        interview:'Interview', rejected:'Rejected'
    };
    if (!confirm('Update status to: ' + labels[status] + '?')) return;

    var xhr = new XMLHttpRequest();
    xhr.onload = function() {
        var res = JSON.parse(this.responseText);
        if (res.success) {
            var badge = document.getElementById('statusBadge_' + appId);
            if (badge) {
                badge.className = 'badge badge-' + status + ' status-badge';
                badge.innerHTML = labels[status];
            }
            showToast('Status updated to: ' + labels[status]);
            // Remove the update panel buttons if rejected
            if (status === 'rejected') {
                setTimeout(function(){ location.reload(); }, 1200);
            }
        } else {
            alert('Could not update status.');
        }
    };
    xhr.open('POST', 'ApplicantController.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('action=update_status_ajax&app_id=' + appId + '&status=' + status);
}
</script>

<?php include __DIR__ . '/../footer.php'; ?>