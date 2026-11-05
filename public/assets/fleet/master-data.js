/*
 * Master Data drawer.
 *
 * Create/edit links inside the Master Data area open in the shared offcanvas
 * instead of navigating away. The form itself renders in an iframe (with
 * `?drawer=1`) so the existing page scripts, file uploads and validation
 * redirects keep working unchanged. If the iframe ends up on a different
 * pathname the form was submitted successfully, so the parent page reloads
 * in place and the user keeps their current context/tab.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var drawerEl = document.getElementById('masterDataDrawer');
        var frame = document.getElementById('masterDataDrawerFrame');
        var titleEl = document.getElementById('masterDataDrawerLabel');
        if (!drawerEl || !frame || !window.bootstrap || !window.bootstrap.Offcanvas) {
            return;
        }

        var drawer = window.bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
        var expectedPath = null;

        function isFormLink(link) {
            if (link.hasAttribute('data-no-drawer') || link.target === '_blank') {
                return false;
            }
            var url = new URL(link.href, window.location.origin);
            if (url.origin !== window.location.origin) {
                return false;
            }
            return /\/(create|edit)$/.test(url.pathname) || /\/create$/.test(url.pathname);
        }

        function openDrawer(link) {
            var url = new URL(link.href, window.location.origin);
            expectedPath = url.pathname;
            url.searchParams.set('drawer', '1');
            frame.src = 'about:blank';
            frame.src = url.toString();

            if (titleEl) {
                var label = (link.getAttribute('data-drawer-title') || link.getAttribute('title') || link.textContent || '').trim();
                titleEl.textContent = label || 'Master Data';
            }

            drawer.show();
        }

        document.addEventListener('click', function (event) {
            var link = event.target.closest('a');
            if (!link || !link.href || !link.closest('.master-data-content')) {
                return;
            }
            if (!isFormLink(link)) {
                return;
            }
            event.preventDefault();
            openDrawer(link);
        });

        frame.addEventListener('load', function () {
            if (!expectedPath || frame.src === 'about:blank') {
                return;
            }
            var path;
            try {
                path = frame.contentWindow.location.pathname;
            } catch (e) {
                return;
            }
            if (path && path !== 'blank' && path !== 'about:blank' && path !== expectedPath) {
                window.location.reload();
            }
        });
    });
})();
