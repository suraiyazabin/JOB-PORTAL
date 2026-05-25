<?php
// $action is set by CandidateController: 'search' | 'view' | 'outreach' | 'outreach_history'

$pageTitle  = 'Candidates';
$activePage = 'candidates';
include __DIR__ . '/../header.php';
?>

<?php if ($action === 'search'): ?>
<!-- ══ SEARCH PAGE (AJAX-powered) ════════════════════════════ -->
<div class="page-header">
    <h1>Candidate Search</h1>
    <a href="CandidateController.php?action=outreach_history" class="btn btn-secondary">Outreach History</a>
</div>

<!-- Search Filters -->
<div class="card">
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:14px;align-items:flex-end">
        <div class="form-group" style="margin-bottom:0">
            <label>Keyword</label>
            <input type="text" id="srchKeyword" placeholder="Skills, headline..."
                   onkeyup="if(event.key==='Enter') searchCandidates()">
        </div>
        <div class="form-group" style="margin-bottom:0">
            <label>Location</label>
            <input type="text" id="srchLocation" placeholder="City, country..."
                   onkeyup="if(event.key==='Enter') searchCandidates()">
        </div>
        <div class="form-group" style="margin-bottom:0">
            <label>Experience Level</label>
            <select id="srchExp">
                <option value="">All Levels</option>
                <option value="entry">Entry (0–2 yrs)</option>
                <option value="mid">Mid (3–6 yrs)</option>
                <option value="senior">Senior (7+ yrs)</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0">
            <label>Education</label>
            <select id="srchEdu">
                <option value="">Any</option>
                <option value="High School">High School</option>
                <option value="Bachelor's">Bachelor's</option>
                <option value="Master's">Master's</option>
                <option value="PhD">PhD</option>
            </select>
        </div>
        <div>
            <button onclick="searchCandidates()" class="btn btn-primary">Search</button>
        </div>
    </div>
</div>

<!-- Results Box (populated by AJAX) -->
<div class="card">
    <h2>Results</h2>
    <div id="candidateResults">
        <p class="text-muted">Enter keywords above and click Search to find candidates.</p>
    </div>
</div>

<?php elseif ($action === 'view'): ?>
<!-- ══ SEEKER PUBLIC PROFILE ══════════════════════════════════ -->
<div class="page-header">
    <h1>Candidate Profile</h1>
    <div class="flex" style="gap:10px">
        <a href="CandidateController.php" class="btn btn-secondary">← Search</a>
        <a href="CandidateController.php?action=outreach&id=<?php echo $seeker['id']; ?>"
           class="btn btn-primary">Send Outreach</a>
    </div>
</div>

<div class="card">
    <div class="flex" style="gap:20px;align-items:flex-start;margin-bottom:22px">
        <?php if (!empty($seeker['profile_pic'])): ?>
            <img src="../<?php echo htmlspecialchars($seeker['profile_pic']); ?>"
                 style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid #e5e7eb">
        <?php else: ?>
            <div style="width:72px;height:72px;border-radius:50%;background:#1e3a5f;
                        display:flex;align-items:center;justify-content:center;
                        font-size:1.8rem;color:#fff;flex-shrink:0">
                <?php echo strtoupper(substr($seeker['name'], 0, 1)); ?>
            </div>
        <?php endif; ?>
        <div>
            <h2 style="font-size:1.4rem;color:#1e3a5f;margin-bottom:4px">
                <?php echo htmlspecialchars($seeker['name']); ?>
            </h2>
            <?php if ($seeker['headline']): ?>
                <p style="color:#4b5563"><?php echo htmlspecialchars($seeker['headline']); ?></p>
            <?php endif; ?>
            <div class="flex" style="gap:14px;margin-top:6px;font-size:.85rem;color:#6b7280">
                <span>📧 <?php echo htmlspecialchars($seeker['email']); ?></span>
                <?php if ($seeker['preferred_location']): ?>
                    <span>📍 <?php echo htmlspecialchars($seeker['preferred_location']); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="form-row" style="margin-bottom:18px">
        <div>
            <label style="color:#6b7280;font-size:.78rem;text-transform:uppercase">Experience</label>
            <p><?php echo $seeker['years_experience'] ?? '0'; ?> year(s)</p>
        </div>
        <div>
            <label style="color:#6b7280;font-size:.78rem;text-transform:uppercase">Education</label>
            <p><?php echo htmlspecialchars($seeker['education_level'] ?? '—'); ?></p>
        </div>
        <div>
            <label style="color:#6b7280;font-size:.78rem;text-transform:uppercase">Expected Salary</label>
            <p><?php echo $seeker['expected_salary'] ? '৳' . number_format($seeker['expected_salary']) : '—'; ?></p>
        </div>
    </div>

    <?php if ($seeker['summary']): ?>
        <div class="form-group">
            <label style="color:#6b7280;font-size:.78rem;text-transform:uppercase">Summary</label>
            <p><?php echo nl2br(htmlspecialchars($seeker['summary'])); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($seeker['skills']): ?>
        <div class="form-group">
            <label style="color:#6b7280;font-size:.78rem;text-transform:uppercase">Skills</label>
            <div class="flex" style="flex-wrap:wrap;gap:6px">
                <?php foreach (explode(',', $seeker['skills']) as $sk): ?>
                    <span style="background:#dbeafe;color:#1e40af;padding:3px 10px;border-radius:20px;font-size:.83rem">
                        <?php echo htmlspecialchars(trim($sk)); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($seeker['resume_path']): ?>
        <div style="margin-top:16px">
            <a href="../<?php echo htmlspecialchars($seeker['resume_path']); ?>"
               target="_blank" class="btn btn-success">📄 Download Resume</a>
        </div>
    <?php endif; ?>
</div>

<?php elseif ($action === 'outreach'): ?>
<!-- ══ OUTREACH FORM ══════════════════════════════════════════ -->
<div class="page-header">
    <h1>Send Outreach</h1>
    <a href="CandidateController.php?action=view&id=<?php echo $seeker['id']; ?>"
       class="btn btn-secondary">← Back to Profile</a>
</div>

<div class="card" style="max-width:640px">
    <div style="background:#f0f4ff;border-radius:8px;padding:14px 18px;margin-bottom:22px">
        <strong><?php echo htmlspecialchars($seeker['name']); ?></strong>
        <?php if ($seeker['headline']): ?>
            — <?php echo htmlspecialchars($seeker['headline']); ?>
        <?php endif; ?>
    </div>

    <form action="CandidateController.php" method="post">
        <input type="hidden" name="action"    value="outreach_save">
        <input type="hidden" name="seeker_id" value="<?php echo $seeker['id']; ?>">

        <div class="form-group">
            <label>Related Job <small>(optional)</small></label>
            <select name="job_id">
                <option value="">— General inquiry —</option>
                <?php foreach ($jobs as $job): ?>
                    <?php if ($job['status'] === 'active'): ?>
                        <option value="<?php echo $job['id']; ?>">
                            <?php echo htmlspecialchars($job['title']); ?>
                            <?php if ($job['client_name']): ?>
                                (<?php echo htmlspecialchars($job['client_name']); ?>)
                            <?php endif; ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Message *</label>
            <textarea name="message" rows="6" required
                      placeholder="Hi [Name], I came across your profile and believe you would be a great fit for..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Send Outreach Message</button>
    </form>
</div>

<?php else: // outreach_history ?>
<!-- ══ OUTREACH HISTORY ═══════════════════════════════════════ -->
<div class="page-header">
    <h1>Outreach History</h1>
    <a href="CandidateController.php" class="btn btn-secondary">← Search Candidates</a>
</div>

<?php if (empty($outreach)): ?>
    <div class="card text-center" style="padding:48px">
        <p class="text-muted">No outreach messages sent yet.</p>
        <a href="CandidateController.php" class="btn btn-primary" style="margin-top:14px">Search Candidates</a>
    </div>
<?php else: ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Job</th>
                    <th>Message Preview</th>
                    <th>Status</th>
                    <th>Sent</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($outreach as $o): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($o['seeker_name']); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($o['seeker_email']); ?></small>
                    </td>
                    <td><?php echo $o['job_title'] ? htmlspecialchars($o['job_title']) : '<span class="text-muted">General</span>'; ?></td>
                    <td>
                        <small><?php echo htmlspecialchars(substr($o['message'], 0, 80)); ?>...</small>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $o['status']; ?>">
                            <?php echo ucfirst($o['status']); ?>
                        </span>
                    </td>
                    <td><small><?php echo date('d M Y', strtotime($o['sent_at'])); ?></small></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<?php include __DIR__ . '/../footer.php'; ?>