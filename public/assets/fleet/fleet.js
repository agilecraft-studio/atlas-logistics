(function () {
    'use strict';
    /* Custom x-fleet.modal close buttons (Bootstrap modals use data-bs-dismiss). */
    document.querySelectorAll('[data-fleet-close]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var m = btn.closest('.fleet-modal');
            if (m) m.classList.remove('open');
        });
    });
    /* Delete-confirm prompt for forms that opt in. Delegated so rows injected by
       DataTables after page load also get the confirmation. */
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('form[data-fleet-confirm]');
        if (form && !confirm(form.getAttribute('data-fleet-confirm') || 'Delete this record?')) {
            e.preventDefault();
        }
    });
})();
