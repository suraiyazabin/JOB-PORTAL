// Job Portal — external.js

// Auto-hide flash alerts after 4s
document.addEventListener('DOMContentLoaded', function () {
  var alerts = document.querySelectorAll('.alert-success, .alert-warning');
  alerts.forEach(function (el) {
    if (!el.id) {
      setTimeout(function () {
        el.style.transition = 'opacity .5s';
        el.style.opacity = '0';
        setTimeout(function () { el.remove(); }, 500);
      }, 4000);
    }
  });

  // Close modal on outside click
  var modal = document.getElementById('edit-modal');
  if (modal) {
    modal.addEventListener('click', function (e) {
      if (e.target === modal) modal.style.display = 'none';
    });
  }

  // Confirm delete buttons
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm(this.dataset.confirm)) e.preventDefault();
    });
  });
});