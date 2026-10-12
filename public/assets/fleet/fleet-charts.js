/*
 * Atlas Logistics — fleet dashboard chart bootstrap.
 * Extracted from the retired fleet.js shell. Renders ONLY when ApexCharts and
 * the chart containers are present. ALL colors are resolved at runtime from
 * tokens.css custom properties (getComputedStyle) — no hardcoded hex here.
 *
 *   Revenue line  = --chart-1 (navy)
 *   Trip costs    = --chart-2 (amber)
 *   Trip-status donut = token list supplied by the blade via the
 *                 `data-chart-colors` attribute (comma-separated var names),
 *                 resolved against tokens.css.
 */
(function () {
    'use strict';

    function token(name) {
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    }

    function readIsland() {
        var island = document.getElementById('fleet-dashboard-data');
        if (!island) return {};
        try { return JSON.parse(island.textContent) || {}; } catch (e) { return {}; }
    }

    function donutColors(el) {
        var raw = (el.getAttribute('data-chart-colors') || '').trim();
        var fallback = ['--chart-1', '--chart-2', '--chart-3', '--chart-4', '--chart-5', '--chart-6'];
        var names = raw ? raw.split(',').map(function (s) { return s.trim(); }) : fallback;
        return names.map(function (n) { return n.indexOf('--') === 0 ? token(n) : n; })
                    .filter(Boolean);
    }

    var data = readIsland();

    var revenueEl = document.getElementById('revenueCostsChart');
    if (revenueEl && window.ApexCharts) {
        new ApexCharts(revenueEl, {
            chart: { type: 'area', height: 210, toolbar: { show: false }, zoom: { enabled: false }, fontFamily: 'inherit' },
            series: [{ name: 'Revenue', data: data.revenue_chart || [] }, { name: 'Trip Costs', data: data.cost_chart || [] }],
            colors: [token('--chart-1'), token('--chart-2')],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2.5 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.22, opacityTo: 0.02 } },
            xaxis: { categories: data.chart_months || [], axisBorder: { show: false }, axisTicks: { show: false } },
            grid: { borderColor: token('--atlas-border'), strokeDashArray: 3 }
        }).render();
    }

    var statusEl = document.getElementById('tripStatusChart');
    if (statusEl && window.ApexCharts) {
        new ApexCharts(statusEl, {
            chart: { type: 'donut', height: 190, fontFamily: 'inherit' },
            series: data.trip_status_values || [],
            labels: data.trip_status_labels || [],
            colors: donutColors(statusEl)
        }).render();
    }
})();
