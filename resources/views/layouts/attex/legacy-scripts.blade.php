{{-- Classic (non-module) jQuery + legacy Atlas plugins.
     ---------------------------------------------------------------------------
     These scripts emit as classic <script> tags and load BEFORE the deferred
     Vite modules (@vite app.js / layout.js) and before the legacy inline
     scripts (@yield('script')).

     Why classic? Vite emits <script type="module"> which is deferred until
     after the document has been parsed. Legacy views emit inline scripts at
     the end of body, and those scripts evaluate at PARSE time - they need a
     synchronous global window.jQuery, which only a classic <script> tag can
     guarantee before the vite modules take control.

     As a single global jQuery (plugins-jquery.min.js, jQuery 3.3.1) is loaded
     here, the Attex modules (app.js / layout.js) attach to that same global
     via select2(window, jQuery) and $(document).ready(...), keeping every
     DataTables / Select2 plugin attached to one instance.

     The Bootstrap 4 -> 5 shim below is the canonical file at
     resources/js/legacy-compat.js. It is inlined here as a classic script so
     it runs at parse time, before the inline legacy scripts. --}}

<script src="{{ asset('assets/js/plugins-jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/moment.js') }}"></script>

<script>
/*
 * legacy-compat.js (inlined, see resources/js/legacy-compat.js for the
 * canonical source). Runs at parse time, before Vite's deferred modules.
 */
(function ($) {
    'use strict';

    var RENAMED_ATTRS = [ 'toggle', 'target', 'dismiss' ];

    function upgradeAttributes(root) {
        if (!root || !root.querySelectorAll) {
            return;
        }
        RENAMED_ATTRS.forEach(function (name) {
            var legacy = root.querySelectorAll('[data-' + name + ']');
            Array.prototype.forEach.call(legacy, function (el) {
                var modern = 'data-bs-' + name;
                if (!el.hasAttribute(modern)) {
                    el.setAttribute(modern, el.getAttribute('data-' + name));
                }
                el.removeAttribute('data-' + name);
            });
        });
    }

    var PLUGINS = {
        modal: 'Modal', tab: 'Tab', tooltip: 'Tooltip', alert: 'Alert',
        collapse: 'Collapse', popover: 'Popover', carousel: 'Carousel',
        toast: 'Toast', offcanvas: 'Offcanvas', dropdown: 'Dropdown',
        scrollspy: 'ScrollSpy'
    };

    function resolveCtor(name, callback) {
        var Ctor = window.bootstrap && window.bootstrap[name];
        if (!Ctor) {
            if (!window.__ATTEX_BOOTSTRAP_RESOLVING) {
                window.__ATTEX_BOOTSTRAP_RESOLVING = true;
                setTimeout(function () {
                    window.__ATTEX_BOOTSTRAP_RESOLVING = false;
                    resolveCtor(name, callback);
                }, 25);
            }
            return;
        }
        callback(Ctor);
    }

    function installPluginBridge($) {
        Object.keys(PLUGINS).forEach(function (name) {
            if ($[name] && $.fn[name]) {
                return;
            }
            $[name] = function (action) {
                var args = Array.prototype.slice.call(arguments, 1);
                return this.each(function () {
                    function run(Ctor) {
                        var inst = Ctor.getOrCreateInstance(this);
                        var fn = inst[action];
                        if (action === undefined || typeof action === 'object') {
                            Ctor.getOrCreateInstance(this, action);
                            return;
                        }
                        if (typeof action === 'string') {
                            if (action === 'dispose' || action === 'destroy') {
                                if (typeof inst.dispose === 'function') {
                                    inst.dispose();
                                }
                            } else if (typeof fn === 'function') {
                                fn.apply(inst, args);
                            }
                        }
                    }
                    resolveCtor(PLUGINS[name], run);
                });
            };
        });
    }

    installPluginBridge($);

    upgradeAttributes(document);

    if (typeof MutationObserver === 'function') {
        new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                Array.prototype.forEach.call(mutation.addedNodes || [], function (node) {
                    if (node.nodeType === 1) {
                        upgradeAttributes(node);
                    }
                });
            });
        }).observe(document.documentElement, { childList: true, subtree: true });
    }

    window.AtlasLegacyCompat = {
        upgradeAttributes: upgradeAttributes,
        upgradeDocument: function () { upgradeAttributes(document); }
    };
})(jQuery);
</script>

<script src="{{ asset('assets/js/cdn-jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins-dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/js/cdn-dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('assets/js/cdn-pdfmake.min.js') }}"></script>
<script src="{{ asset('assets/js/cdn-vfs_fonts.js') }}"></script>
<script src="{{ asset('assets/js/cdn-jszip.min.js') }}"></script>
<script src="{{ asset('assets/js/cdn-buttons.html5.min.js') }}"></script>
<script src="{{ asset('assets/js/cdn-buttons.print.min.js') }}"></script>
<script src="{{ asset('assets/js/cdn-dataTables.responsive.min.js') }}"></script>

<script src="{{ asset('assets/js/plugins-select2.full.min.js') }}"></script>

<script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('assets/js/datetimepicker.js') }}"></script>
<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>

<script src="{{ asset('assets/js/plugins-fullcalendar.min.js') }}"></script>

<script src="{{ asset('assets/js/cdn-Chart.bundle.min.js') }}"></script>

<script src="{{ asset('assets/js/plugins-fastclick.js') }}"></script>
<script src="{{ asset('assets/js/pnotify.custom.min.js') }}"></script>
