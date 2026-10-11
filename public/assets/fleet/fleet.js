(function () {
    'use strict';
    var menuButton = document.querySelector('.fleet-menu-button');
    var sidebar = document.querySelector('.fleet-sidebar');
    if (menuButton && sidebar) {
        menuButton.addEventListener('click', function () {
            if (window.innerWidth <= 650) {
                sidebar.style.display = sidebar.style.display === 'flex' ? 'none' : 'flex';
            } else {
                sidebar.classList.toggle('fleet-sidebar-collapsed');
            }
        });
    }
    document.querySelectorAll('[data-fleet-close]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var m = btn.closest('.fleet-modal');
            if (m) m.classList.remove('open');
            var m2 = btn.closest('.modal');
            if (m2) m2.classList.remove('show');
        });
    });
    document.querySelectorAll('[data-fleet-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm(form.getAttribute('data-fleet-confirm') || 'Delete this record?')) e.preventDefault();
        });
    });
    var island = document.getElementById('fleet-dashboard-data');
    var data = {};
    if (island) { try { data = JSON.parse(island.textContent); } catch (e) { data = {}; } }
    var revenueEl = document.querySelector('#revenueCostsChart');
    if (revenueEl && window.ApexCharts) {
        new ApexCharts(revenueEl, {
            chart: { type: 'area', height: 210, toolbar: { show: false }, zoom: { enabled: false }, fontFamily: 'inherit' },
            series: [{ name: 'Revenue', data: data.revenue_chart || [] }, { name: 'Trip Costs', data: data.cost_chart || [] }],
            colors: ['#10a66a', '#1688e8'], dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2.5 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.22, opacityTo: 0.02 } },
            xaxis: { categories: data.chart_months || [], axisBorder: { show: false }, axisTicks: { show: false } },
            grid: { borderColor: '#edf2f7', strokeDashArray: 3 }
        }).render();
    }
    var statusEl = document.querySelector('#tripStatusChart');
    if (statusEl && window.ApexCharts) {
        new ApexCharts(statusEl, {
            chart: { type: 'donut', height: 190, fontFamily: 'inherit' },
            series: data.trip_status_values || [],
            labels: data.trip_status_labels || [],
            colors: ['#16b981', '#f59e0b', '#1688e8', '#079ca4', '#ef4444']
        }).render();
    }
})();
