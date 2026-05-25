// ============================================================
//  Job Portal — main.js
//  Pattern: same as faculty's E-Commerce-Store external.js
//  Functions: validation helpers, AJAX (XMLHttpRequest style)
// ============================================================

// ── Utility helpers ──────────────────────────────────────────
function showError(id, msg) {
    var el = document.getElementById(id);
    if (el) el.innerHTML = msg;
}

function clearErrors(ids) {
    ids.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.innerHTML = '';
    });
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// ── Login validation ──────────────────────────────────────────
function validateLogin(form) {
    var valid = true;
    clearErrors(['loginEmailErr', 'loginPassErr', 'loginRoleErr']);

    if (!form.email.value.trim()) {
        showError('loginEmailErr', 'Email is required.');
        valid = false;
    } else if (!isValidEmail(form.email.value.trim())) {
        showError('loginEmailErr', 'Enter a valid email address.');
        valid = false;
    }
    if (!form.password.value) {
        showError('loginPassErr', 'Password is required.');
        valid = false;
    }
    if (!form.role.value) {
        showError('loginRoleErr', 'Please select your role.');
        valid = false;
    }
    return valid;
}

// ── Employer register validation ──────────────────────────────
function validateEmployerRegister(form) {
    var valid = true;
    clearErrors(['erNameErr', 'erEmailErr', 'erPassErr', 'erConfirmErr', 'erCompanyErr', 'erAddrErr']);

    if (!form.name.value.trim()) {
        showError('erNameErr', 'Full name is required.');
        valid = false;
    }
    if (!form.email.value.trim() || !isValidEmail(form.email.value.trim())) {
        showError('erEmailErr', 'A valid email is required.');
        valid = false;
    }
    if (form.password.value.length < 6) {
        showError('erPassErr', 'Password must be at least 6 characters.');
        valid = false;
    }
    if (form.password.value !== form.confirm_password.value) {
        showError('erConfirmErr', 'Passwords do not match.');
        valid = false;
    }
    if (!form.company_name.value.trim()) {
        showError('erCompanyErr', 'Company name is required.');
        valid = false;
    }
    if (!form.address.value.trim()) {
        showError('erAddrErr', 'Address is required.');
        valid = false;
    }
    return valid;
}

// ── Recruiter register validation ─────────────────────────────
function validateRecruiterRegister(form) {
    var valid = true;
    clearErrors(['rrNameErr', 'rrEmailErr', 'rrPassErr', 'rrConfirmErr', 'rrAgencyErr']);

    if (!form.name.value.trim()) {
        showError('rrNameErr', 'Full name is required.');
        valid = false;
    }
    if (!form.email.value.trim() || !isValidEmail(form.email.value.trim())) {
        showError('rrEmailErr', 'A valid email is required.');
        valid = false;
    }
    if (form.password.value.length < 6) {
        showError('rrPassErr', 'Password must be at least 6 characters.');
        valid = false;
    }
    if (form.password.value !== form.confirm_password.value) {
        showError('rrConfirmErr', 'Passwords do not match.');
        valid = false;
    }
    if (!form.agency_name.value.trim()) {
        showError('rrAgencyErr', 'Agency name is required.');
        valid = false;
    }
    return valid;
}

// ── Job form validation ───────────────────────────────────────
function validateJobForm(form) {
    var valid = true;
    clearErrors(['jTitleErr', 'jCatErr', 'jLocErr', 'jDeadlineErr', 'jSalaryErr']);

    if (!form.title.value.trim()) {
        showError('jTitleErr', 'Job title is required.');
        valid = false;
    }
    if (!form.category_id.value) {
        showError('jCatErr', 'Please select a category.');
        valid = false;
    }
    if (!form.location.value.trim()) {
        showError('jLocErr', 'Location is required.');
        valid = false;
    }
    if (!form.deadline.value) {
        showError('jDeadlineErr', 'Application deadline is required.');
        valid = false;
    }
    var min = parseFloat(form.salary_min.value);
    var max = parseFloat(form.salary_max.value);
    if (!isNaN(min) && !isNaN(max) && max < min) {
        showError('jSalaryErr', 'Max salary must be greater than min salary.');
        valid = false;
    }
    return valid;
}

// ── Password match check ──────────────────────────────────────
function checkPassMatch() {
    var np = document.getElementById('newPass');
    var cp = document.getElementById('confirmPass');
    var err = document.getElementById('confirmPassErr');
    if (np && cp && err) {
        if (np.value !== cp.value) {
            err.innerHTML = 'Passwords do not match.';
            return false;
        }
    }
    return true;
}

// ── AJAX: Toggle job status (employer & recruiter) ────────────
// Called from jobs list table — toggles active ↔ closed
function toggleJobStatus(jobId, currentStatus, controllerPath) {
    var newStatus = currentStatus === 'active' ? 'closed' : 'active';

    var xhr = new XMLHttpRequest();
    xhr.onload = function () {
        var res = JSON.parse(this.responseText);
        if (res.success) {
            // Update badge and button text in the row
            var badge  = document.getElementById('statusBadge_' + jobId);
            var btn    = document.getElementById('toggleBtn_'  + jobId);
            if (badge) {
                badge.className = 'badge badge-' + newStatus;
                badge.innerHTML = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            }
            if (btn) {
                btn.innerHTML = newStatus === 'active' ? 'Close Job' : 'Repost';
                btn.setAttribute('onclick',
                    "toggleJobStatus(" + jobId + ", '" + newStatus + "', '" + controllerPath + "')");
            }
        } else {
            alert('Could not update status. Please try again.');
        }
    };
    xhr.open('POST', controllerPath, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('action=toggle_status&job_id=' + jobId + '&status=' + newStatus);
}

// ── AJAX: Update applicant status (employer) ──────────────────
function updateAppStatus(selectEl, appId) {
    var newStatus = selectEl.value;
    var xhr = new XMLHttpRequest();
    xhr.onload = function () {
        var res = JSON.parse(this.responseText);
        var row = document.getElementById('appRow_' + appId);
        if (res.success && row) {
            var badge = row.querySelector('.status-badge');
            if (badge) {
                badge.className = 'badge badge-' + newStatus + ' status-badge';
                badge.innerHTML = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            }
            showToast('Status updated to: ' + newStatus);
        } else {
            alert('Could not update status.');
        }
    };
    xhr.open('POST', 'ApplicantController.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('action=update_status_ajax&app_id=' + appId + '&status=' + newStatus);
}

// ── AJAX: Update pipeline status (recruiter) ──────────────────
function updatePipelineStatus(selectEl, appId) {
    var newStatus = selectEl.value;
    var xhr = new XMLHttpRequest();
    xhr.onload = function () {
        var res = JSON.parse(this.responseText);
        var row = document.getElementById('pipeRow_' + appId);
        if (res.success && row) {
            var badge = row.querySelector('.status-badge');
            if (badge) {
                badge.className = 'badge badge-' + newStatus + ' status-badge';
                badge.innerHTML = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            }
            showToast('Pipeline stage updated.');
        } else {
            alert('Could not update pipeline stage.');
        }
    };
    xhr.open('POST', 'PipelineController.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('action=update_status_ajax&app_id=' + appId + '&status=' + newStatus);
}

// ── AJAX: Candidate search (recruiter) ───────────────────────
function searchCandidates() {
    var keyword  = document.getElementById('srchKeyword').value;
    var location = document.getElementById('srchLocation').value;
    var expLevel = document.getElementById('srchExp').value;
    var eduLevel = document.getElementById('srchEdu').value;

    var resultBox = document.getElementById('candidateResults');
    resultBox.innerHTML = '<p class="text-muted">Searching...</p>';

    var xhr = new XMLHttpRequest();
    xhr.onload = function () {
        var seekers = JSON.parse(this.responseText);
        if (seekers.length === 0) {
            resultBox.innerHTML = '<p class="text-muted">No candidates found matching your criteria.</p>';
            return;
        }
        var html = '<table><thead><tr>'
            + '<th>Name</th><th>Headline</th><th>Skills</th>'
            + '<th>Experience</th><th>Location</th><th>Expected Salary</th><th>Action</th>'
            + '</tr></thead><tbody>';
        seekers.forEach(function(s) {
            html += '<tr>'
                + '<td>' + escHtml(s.name) + '</td>'
                + '<td>' + escHtml(s.headline || '—') + '</td>'
                + '<td><small>' + escHtml(s.skills || '—') + '</small></td>'
                + '<td>' + (s.years_experience || '0') + ' yrs</td>'
                + '<td>' + escHtml(s.preferred_location || '—') + '</td>'
                + '<td>' + (s.expected_salary ? '৳' + Number(s.expected_salary).toLocaleString() : '—') + '</td>'
                + '<td>'
                + '<a href="CandidateController.php?action=view&id=' + s.user_id + '" class="btn btn-sm btn-info">View</a> '
                + '<a href="CandidateController.php?action=outreach&id=' + s.user_id + '" class="btn btn-sm btn-success">Outreach</a>'
                + '</td>'
                + '</tr>';
        });
        html += '</tbody></table>';
        resultBox.innerHTML = html;
    };
    xhr.open('GET', 'CandidateController.php?action=search_ajax'
        + '&keyword=' + encodeURIComponent(keyword)
        + '&location=' + encodeURIComponent(location)
        + '&exp=' + encodeURIComponent(expLevel)
        + '&edu=' + encodeURIComponent(eduLevel), true);
    xhr.send();
}

// ── Toast notification ────────────────────────────────────────
function showToast(msg) {
    var t = document.getElementById('toast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'toast';
        t.style.cssText = 'position:fixed;bottom:28px;right:28px;background:#1e3a5f;color:#fff;'
            + 'padding:12px 22px;border-radius:8px;font-size:.9rem;z-index:9999;'
            + 'box-shadow:0 4px 12px rgba(0,0,0,.25);transition:opacity .4s';
        document.body.appendChild(t);
    }
    t.innerHTML = msg;
    t.style.opacity = '1';
    clearTimeout(t._timeout);
    t._timeout = setTimeout(function() { t.style.opacity = '0'; }, 2800);
}

// ── Escape HTML (for AJAX-rendered content) ───────────────────
function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}