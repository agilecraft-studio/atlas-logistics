/**
 * legacy-compat.js
 *
 * Bootstrap 4 -> Bootstrap 5 runtime shim for admin views that have not yet
 * been converted to Bootstrap 5 (Attex) markup.
 *
 * SCOPE: temporary migration shim, loaded from layouts/attex.blade.php for the
 * admin shell only. Two jobs:
 *
 *  1. Rewrite the Bootstrap 4 `data-*` trigger attributes to the Bootstrap 5
 *     `data-bs-*` names, so declarative markup (modals, dropdowns, tabs,
 *     collapses, tooltips) keeps working without touching each view.
 *
 *  2. Restore the jQuery plugin API that Bootstrap 5 removed
 *     ($.fn.modal / tab / tooltip / alert / collapse / popover / carousel /
 *     toast / offcanvas), because legacy inline scripts call
 *     $('#x').modal('show') and friends directly.
 *
 * As views are converted to real Bootstrap 5 markup, the corresponding rules
 * here become dead and MUST be deleted with them.
 */

import $ from 'jquery';

(function ($) {
    'use strict';

    // -----------------------------------------------------------------------
    // 1. data-* -> data-bs-* attribute upgrade
    // -----------------------------------------------------------------------

    // Only the three Bootstrap trigger attributes the legacy views actually
    // use. Extend this list only when a converted view is found to need more.
    var RENAMED_ATTRS = ['toggle', 'target', 'dismiss'];

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

    // Bootstrap 5 delegates clicks on document and reads the attribute at
    // event time, so upgrading before user interaction is sufficient.
    function upgradeDocument() {
        upgradeAttributes(document);
    }

    // -----------------------------------------------------------------------
    // 2. jQuery plugin API bridge (Bootstrap 5 removed it)
    // -----------------------------------------------------------------------
    //
    // Legacy inline scripts call e.g. $('#x').modal('show'), .tab('show'),
    // .alert('close'), .tooltip(). Bootstrap 5 is class-based only, so each
    // bridge maps the old jQuery call onto the equivalent Bootstrap 5 class.
    //
    // The Bootstrap 5 constructor is resolved LAZILY, at invocation time. This
    // file is emitted as a classic script that runs at parse time, while
    // Bootstrap 5 arrives later inside a deferred Vite module, so
    // window.bootstrap cannot be assumed to exist when the bridge is defined.

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

    /**
     * Maps a Bootstrap 4 jQuery action string onto a Bootstrap 5 method.
     * 'destroy' (BS4) becomes 'dispose' (BS5).
     */
    function resolveAction(instance, action, args) {
        if (action === 'dispose' || action === 'destroy') {
            instance.dispose();
            return;
        }

        if (typeof instance[action] === 'function') {
            instance[action].apply(instance, args);
        }
    }

    function installPluginBridge(jq) {
        if (!jq || !jq.fn) {
            return;
        }

        Object.keys(PLUGINS).forEach(function (name) {
            if (jq.fn[name]) {
                return;
            }

            jq.fn[name] = function (action) {
                var args = Array.prototype.slice.call(arguments, 1);

                return this.each(function () {
                    var Ctor = window.bootstrap && window.bootstrap[PLUGINS[name]];

                    if (!Ctor) {
                        return;
                    }

                    // No argument, or an options object: initialise.
                    if (action === undefined || typeof action === 'object') {
                        Ctor.getOrCreateInstance(this, action);
                        return;
                    }

                    if (typeof action === 'string') {
                        resolveAction(Ctor.getOrCreateInstance(this), action, args);
                    }
                });
            };
        });
    }

    // -----------------------------------------------------------------------
    // 3. Initialisation
    // -----------------------------------------------------------------------
    //
    // Legacy inline scripts are classic (non-module) scripts that run at parse
    // time, while Vite emits this file as type="module" (deferred). So this
    // file is ALSO emitted as a classic script by the layout (see
    // layouts/attex/legacy-scripts.blade.php) and must therefore not depend on
    // DOMContentLoaded having fired.

    // Bootstrap 5 is exposed on window.bootstrap by resources/js/app.js. When
    // this runs before app.js, install the bridge as soon as it appears.
    function tryInstall() {
        installPluginBridge($);
    }

    if (window.bootstrap) {
        tryInstall();
    } else {
        // Poll briefly: app.js is a deferred module that runs after parsing.
        var attempts = 0;
        var poll = setInterval(function () {
            attempts += 1;
            if (window.bootstrap) {
                clearInterval(poll);
                tryInstall();
            } else if (attempts > 100) {
                // ~10s; give up rather than loop forever.
                clearInterval(poll);
            }
        }, 100);
    }

    // Upgrade declarative attributes as early as possible.
    upgradeDocument();

    // Views inject markup via AJAX (modals, dynamic rows), so keep upgrading.
    if (typeof MutationObserver === 'function') {
        new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                Array.prototype.forEach.call(mutation.addedNodes, function (node) {
                    if (node.nodeType === 1) {
                        upgradeAttributes(node);
                    }
                });
            });
        }).observe(document.documentElement, { childList: true, subtree: true });
    }

    window.AtlasLegacyCompat = {
        upgradeAttributes: upgradeAttributes,
        upgradeDocument: upgradeDocument
    };

})(jQuery);
