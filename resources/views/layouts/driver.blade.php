@php($appName = AtlasLogistics::get('app_name') ?: 'Atlas Logistics')
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <title>@yield('page_title', 'Driver') · {{ $appName }}</title>
    @include('layouts.attex.head-css')
    <style>
        :root { --driver-radius: 1rem; }
        body.driver-shell { background: #eef1f6; min-height: 100vh; -webkit-tap-highlight-color: transparent; }
        .driver-topbar { position: sticky; top: 0; z-index: 20; display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .75rem 1rem; padding-top: calc(.75rem + env(safe-area-inset-top)); background: #fff; border-bottom: 1px solid rgba(15, 23, 42, .08); }
        .driver-brand { display: flex; align-items: center; gap: .5rem; font-weight: 700; color: #0f172a; }
        .driver-main { width: 100%; max-width: 640px; margin: 0 auto; padding: 1rem 1rem calc(2.5rem + env(safe-area-inset-bottom)); }
        .driver-card { background: #fff; border-radius: var(--driver-radius); border: 1px solid rgba(15, 23, 42, .08); box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }
        .driver-card .card-body { padding: 1.1rem; }
        .driver-trip-link { display: block; color: inherit; text-decoration: none; }
        .driver-trip-link:active { transform: scale(.995); }
        .odometer-input { font-size: clamp(1.9rem, 8vw, 2.6rem); font-weight: 700; letter-spacing: .02em; height: auto; padding: .75rem 1rem; text-align: center; font-variant-numeric: tabular-nums; }
        .kind-tiles, .fuel-tiles { display: grid; gap: .5rem; }
        .kind-tiles { grid-template-columns: repeat(3, 1fr); }
        .fuel-tiles { grid-template-columns: repeat(5, 1fr); }
        .kind-tile, .fuel-tile { display: flex; align-items: center; justify-content: center; min-height: 3.25rem; margin: 0; padding: .5rem .25rem; border: 2px solid #dbe1ea; border-radius: .75rem; background: #fff; color: #334155; font-weight: 600; text-align: center; cursor: pointer; user-select: none; transition: .15s ease; }
        .fuel-tile { min-height: 3.9rem; flex-direction: column; font-size: .9rem; line-height: 1.15; }
        .btn-check:checked + .kind-tile, .btn-check:checked + .fuel-tile { border-color: var(--bs-primary); background: rgba(var(--bs-primary-rgb), .08); color: var(--bs-primary); box-shadow: inset 0 0 0 1px var(--bs-primary); }
        .btn-check:disabled + .kind-tile, .btn-check:disabled + .fuel-tile { opacity: .4; cursor: not-allowed; }
        .btn-check:focus-visible + .kind-tile, .btn-check:focus-visible + .fuel-tile { outline: 2px solid var(--bs-primary); outline-offset: 2px; }
        .driver-save { min-height: 3.75rem; border-radius: .9rem; font-size: 1.15rem; font-weight: 700; }
        .checkpoint-history { list-style: none; margin: 0; padding: 0; }
        .checkpoint-history li { display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem; padding: .6rem 0; border-bottom: 1px dashed rgba(15, 23, 42, .1); }
        .checkpoint-history li:last-child { border-bottom: 0; }
        .sync-pill { font-size: .78rem; }
        .sync-pill.offline { background: #fff3cd; color: #664d03; }
        .field-label { font-weight: 600; color: #334155; margin-bottom: .4rem; }
        .form-control-lg.odometer-input::placeholder { font-size: 1rem; font-weight: 400; }
    </style>
    @yield('css')
</head>
<body class="driver-shell">
    <header class="driver-topbar">
        <div class="driver-brand">
            <i class="bi bi-truck fs-4 text-primary"></i>
            <span>Atlas <span class="text-muted fw-normal">Driver</span></span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span id="sync-pill" class="badge rounded-pill sync-pill d-none"></span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">Log out</button>
            </form>
        </div>
    </header>

    <main class="driver-main">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    <script>
        /* Driver checkpoint queue: entries are stored on the phone first, then sent in order.
           A dropped signal never blocks saving; retries reuse the same client_uuid so the
           server stores each checkpoint exactly once. */
        (function () {
            'use strict';

            var STORAGE_KEY = 'atlas.driver.queue.v1';
            var meta = document.querySelector('meta[name="csrf-token"]');
            var token = meta ? meta.getAttribute('content') : '';
            var flushing = false;

            function read() {
                try {
                    var raw = localStorage.getItem(STORAGE_KEY);
                    var queue = raw ? JSON.parse(raw) : [];
                    return Array.isArray(queue) ? queue : [];
                } catch (e) {
                    return [];
                }
            }

            function write(queue) {
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(queue));
                } catch (e) {
                    /* Storage full or disabled: the request attempt below is the fallback. */
                }
            }

            function remove(key) {
                write(read().filter(function (item) { return item.key !== key; }));
            }

            function uuid() {
                if (window.crypto && typeof crypto.randomUUID === 'function') {
                    try { return crypto.randomUUID(); } catch (e) { /* fall through */ }
                }
                return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                    var r = Math.random() * 16 | 0;
                    var v = c === 'x' ? r : (r & 0x3 | 0x8);
                    return v.toString(16);
                });
            }

            function emit(name, detail) {
                document.dispatchEvent(new CustomEvent(name, { detail: detail }));
            }

            function firstError(body) {
                if (body && body.errors) {
                    for (var field in body.errors) {
                        if (Object.prototype.hasOwnProperty.call(body.errors, field) && body.errors[field].length) {
                            return body.errors[field][0];
                        }
                    }
                }
                return null;
            }

            function setPill() {
                var pill = document.getElementById('sync-pill');
                if (!pill) return;
                var count = read().length;
                pill.classList.remove('d-none');
                if (!count) {
                    pill.classList.add('d-none');
                    pill.textContent = '';
                    return;
                }
                var offline = !navigator.onLine;
                pill.classList.toggle('offline', offline);
                pill.textContent = offline ? count + ' saved · offline' : count + ' sending…';
            }

            function send(item) {
                return fetch(item.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    keepalive: true,
                    body: JSON.stringify(item.payload)
                });
            }

            function flush() {
                if (flushing || !navigator.onLine || !read().length) {
                    setPill();
                    return;
                }
                flushing = true;
                setPill();

                (function next() {
                    var queue = read();
                    if (!queue.length) {
                        flushing = false;
                        setPill();
                        return;
                    }
                    var item = queue[0];

                    send(item).then(function (res) {
                        if (res.ok) {
                            return res.json().catch(function () { return null; }).then(function (body) {
                                remove(item.key);
                                emit('driver:synced', { item: item, response: body });
                                next();
                            });
                        }
                        if (res.status === 422) {
                            return res.json().catch(function () { return null; }).then(function (body) {
                                remove(item.key);
                                var message = (body && body.message) || firstError(body) || 'The server rejected a saved checkpoint.';
                                emit('driver:rejected', { item: item, message: message });
                                next();
                            });
                        }
                        if (res.status === 401 || res.status === 419) {
                            emit('driver:rejected', { item: item, message: 'Your session expired. Please log in again.' });
                            flushing = false;
                            setPill();
                            return;
                        }
                        emit('driver:queue-offline', { item: item });
                        flushing = false;
                        setPill();
                    }).catch(function () {
                        emit('driver:queue-offline', { item: item });
                        flushing = false;
                        setPill();
                    });
                })();
            }

            window.DriverQueue = {
                uuid: uuid,
                all: read,
                forTrip: function (tripId) {
                    return read().filter(function (item) {
                        return Number(item.tripId) === Number(tripId);
                    });
                },
                enqueue: function (item) {
                    var queue = read();
                    queue.push(item);
                    write(queue);
                    setPill();
                    flush();
                },
                flush: flush
            };

            setPill();
            flush();
            window.addEventListener('online', function () {
                setPill();
                flush();
            });
            window.addEventListener('offline', setPill);
        })();
    </script>
    @yield('script')
</body>
</html>
