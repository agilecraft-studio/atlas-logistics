/**
 * legacy-compat.js
 *
 * Bootstrap 4 -> Bootstrap 5 runtime shim for admin views that have not yet
 * been converted to Bootstrap 5 (Attex) markup.
 *
 * PLAIN (non-module) classic script. It is loaded BEFORE the legacy inline
 * scripts (and before the deferred Vite modules that bring in Bootstrap 5)
 * through layouts/attex/legacy-scripts.blade.php. Because it is classic, it
 * must not use `import` and must not depend on DOMContentLoaded.
 *
 * Two jobs (see README for the full migration doc):
 *  1. Rewrite the Bootstrap 4 `data-*` trigger attributes to the Bootstrap 5
 *     `data-bs-*` names, so declarative markup keeps working.
 *  2. Restore the jQuery plugin API that Bootstrap 5 removed
 *     ($.fn.modal / tab / tooltip / alert / collapse / popover / carousel /
 *     toast / offcanvas), so legacy inline scripts calling
 *     $('#x').modal('show') and friends keep working.
 *
 * The Bootstrap 5 constructors are resolved LAZILY, at invocation time. This
 * lets the bridge map onto classes that only arrive inside the deferred
 * Vite modules after parsing.
 *
 * As views are converted to real Bootstrap 5 markup, the corresponding rules
 * here become dead and MUST be deleted with them.
 */

// Classic jQuery 3.3.1 is loaded by layouts/attex/legacy-scripts.blade.php
// before this file, so the global `jQuery` is guaranteed to exist here.

(function ($) {
    'use strict';

    // ---------------------------------------------------------------------
    // 1. data-* -> data-bs-* attribute upgrade
    // ---------------------------------------------------------------------

    // Only the three Bootstrap trigger attributes the legacy views actually
    // use. Extend this list only when a converted view is found to need more.
    var RENAMED_ATTRS = [ 'toggle', 'target', 'dismiss' ];

    function upgradeAttributes(root) {
        if (!root || !root.querySelectorAll) {
            return;
        }

        RENAMED_ATTRS.forEach(function (name) {
            var legacy = root.querySelectorAll('[data-' + name + ']');
            Array.prototype.forEach.call(legacy, function (el) {
                var modern = 'data-bs-' + name;

                // Never clobber an explicit Bootstrap 5 attribute.
                if (!el.hasAttribute(modern)) {
                    el.setAttribute(modern, el.getAttribute('data-' + name));
                }

                el.removeAttribute('data-' + name);
            });
        });
    }

    // ---------------------------------------------------------------------
    // 2. jQuery plugin API bridge for Bootstrap 5 (class-based only)
    // ---------------------------------------------------------------------

    var PLUGINS = {
        modal: 'Modal',
        tab: 'Tab',
        tooltip: 'Tooltip',
        alert: 'Alert',
        collapse: 'Collapse',
        popover: 'Popover',
        carousel: 'Carousel',
        toast: 'Toast',
        offcanvas: 'Offcanvas',
        dropdown: 'Dropdown',
        scrollspy: 'ScrollSpy'
    };

    // Resolve a Bootstrap 5 constructor lazily. Bootstrap arrives inside the
    // deferred Vite modules, so it is not guaranteed to exist when this file
    // runs. Retries until it does.
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
            if ($.fn[name]) {
                return;
            }
            $.fn[name] = function (action) {
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
                                if (typeof inst.dispose === 'function') inst.dispose();
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

    // ---------------------------------------------------------------------
    // 3. Initialisation (run at parse time, before the deferred modules)
    // ---------------------------------------------------------------------

    // Upgrade declarative attributes as early as possible.
    upgradeAttributes(document);

    // Views inject markup via AJAX (modals, dynamic rows), so keep upgrading.
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
