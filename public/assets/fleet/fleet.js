(function () {
    'use strict';
    /* Custom x-fleet.modal close buttons (Bootstrap modals use data-bs-dismiss). */
    document.querySelectorAll('[data-fleet-close]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var m = btn.closest('.fleet-modal');
            if (m) m.classList.remove('open');
        });
    });
    /* Delete-confirm prompt for forms that opt in. */
    document.querySelectorAll('[data-fleet-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm(form.getAttribute('data-fleet-confirm') || 'Delete this record?')) e.preventDefault();
        });
    });
})();
