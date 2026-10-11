{{-- Legacy page-script runtime for views migrated from the attex shell.
     Loads the SAME local jQuery bundle + moment the attex shell used (no CDN),
     plus small shims so legacy inline scripts keep working:
       * CSRF token + AJAX header
       * PNotify toast shim (fleet styled)
       * Bootstrap-style modal show/hide (jQuery + data-toggle/data-bs-toggle)
       * global route/label JS variables used by legacy dependent-dropdown scripts --}}
<script src="{{ asset('assets/js/plugins-jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/moment.js') }}"></script>
{{-- DataTables core + Bootstrap4 styling + Buttons/export stack (same local files as the legacy shell). --}}
<script src="{{ asset('assets/js/cdn-jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins-dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/js/cdn-dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('assets/js/cdn-pdfmake.min.js') }}"></script>
<script src="{{ asset('assets/js/cdn-vfs_fonts.js') }}"></script>
<script src="{{ asset('assets/js/cdn-jszip.min.js') }}"></script>
<script src="{{ asset('assets/js/cdn-buttons.html5.min.js') }}"></script>
<script src="{{ asset('assets/js/cdn-buttons.print.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins-select2.full.min.js') }}"></script>
<script>
window.Laravel = {!! json_encode(['csrfToken' => csrf_token()]) !!};
var getDriverRoute = '{{ url('admin/get_driver') }}';
var getVehicleRoute = '{{ url('admin/get_vehicle') }}';
var prevAddress = '{{ url('admin/prev-address') }}';
var selectDriver = "@lang('fleet.selectDriver')";
var selectCustomer = "@lang('fleet.selectCustomer')";
var selectVehicle = "@lang('fleet.selectVehicle')";
var addCustomer = "@lang('fleet.add_customer')";
var prevAddressLang = "@lang('fleet.prev_addr')";
var fleet_email_already_taken = "@lang('fleet.email_already_taken')";
var driver_comission_amount = "@lang('fleet.enter_amount')";
var driver_comission_percentage = "@lang('fleet.enter_percent')";
var group_id_label = "@lang('fleet.selectGroup')";
var role_id_label = "@lang('fleet.role')";
var current_route = "{{ optional(Route::current())->getName() ?? '' }}";
var base_url = '{{ url('/') }}';
(function ($) {
    'use strict';
    if (!$) { return; }
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken } });

    /* PNotify shim: legacy scripts call new PNotify({title,text,type}). */
    window.PNotify = window.PNotify || function (opts) {
        opts = opts || {};
        var wrap = document.querySelector('.fleet-toast-wrap');
        if (!wrap) { wrap = document.createElement('div'); wrap.className = 'fleet-toast-wrap'; document.body.appendChild(wrap); }
        var t = document.createElement('div');
        var type = opts.type === 'error' ? 'error' : (opts.type === 'info' ? 'info' : (opts.type === 'warning' ? 'warning' : 'success'));
        t.className = 'fleet-toast fleet-toast-' + type;
        var title = opts.title ? '<strong>' + opts.title + '</strong>' : '';
        t.innerHTML = title + '<span>' + (opts.text || '') + '</span>';
        wrap.appendChild(t);
        setTimeout(function () { t.classList.add('fleet-toast-out'); setTimeout(function () { t.remove(); }, 400); }, 4000);
        return { close: function () { t.remove(); } };
    };

    /* Bootstrap-style modal shim (show/hide with bs events + relatedTarget). */
    $.fn.modal = function (action) {
        return this.each(function () {
            var $m = $(this);
            if (action === 'show') {
                var e1 = $.Event('show.bs.modal'); $m.trigger(e1);
                if (e1.isDefaultPrevented()) { return; }
                $m.addClass('show').attr('aria-modal', 'true').attr('aria-hidden', 'false').css('display', 'block');
                document.documentElement.style.overflow = 'hidden';
                $m.trigger($.Event('shown.bs.modal', { relatedTarget: $m.data('fleetRelatedTarget') || null }));
            } else if (action === 'hide') {
                var e2 = $.Event('hide.bs.modal'); $m.trigger(e2);
                if (e2.isDefaultPrevented()) { return; }
                $m.removeClass('show').attr('aria-hidden', 'true').removeAttr('aria-modal').css('display', 'none');
                document.documentElement.style.overflow = '';
                $m.trigger('hidden.bs.modal');
            } else if (action === 'toggle') {
                $m.hasClass('show') ? $m.modal('hide') : $m.modal('show');
            }
        });
    };
    $(document).on('click', '[data-toggle="modal"], [data-bs-toggle="modal"]', function (e) {
        var target = $(this).attr('data-target') || $(this).attr('data-bs-target');
        if (!target || target === '#') { return; }
        e.preventDefault();
        var $t = $(target);
        $t.data('fleetRelatedTarget', this);
        $t.modal('show');
    });
    $(document).on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"]', function () {
        $(this).closest('.modal').modal('hide');
    });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') { $('.modal.show').last().modal('hide'); }
    });
})(window.jQuery);
</script>
