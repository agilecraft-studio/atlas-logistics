/**
 * Fleet Operations Dashboard — external chart initialisation.
 *
 * Reads one JSON island (id="fleet-dashboard-data") populated by the dashboard view.
 * Vendor chart CSS/Js are loaded locally from public/vendor.
 */
(function () {
    'use strict';

    var island = document.getElementById('fleet-dashboard-data');
    var data = {};
    if (island) {
        try {
            data = JSON.parse(island.textContent);
        } catch (e) {
            data = {};
        }
    }

    // --- Revenue vs Trip Costs (area chart) -------------------------------------
    var revenueElement = document.querySelector('#revenueCostsChart');
    if (revenueElement && window.ApexCharts) {
        new ApexCharts(revenueElement, {
            chart: { type: 'area', height: 210, toolbar: { show: false }, zoom: { enabled: false }, fontFamily: 'inherit' },
            series: [
                { name: 'Revenue', data: data.revenue_chart || [] },
                { name: 'Trip Costs', data: data.cost_chart || [] }
            ],
            colors: ['#10a66a', '#1688e8'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2.5 },
            fill: { type: 'gradient', gradient: { opacityFrom: .22, opacityTo: .02 } },
            markers: { size: 3, hover: { size: 5 } },
            xaxis: {
                categories: data.chart_months || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: '#7890a8', fontSize: '10px' } }
            },
            yaxis: {
                min: 0,
                labels: { formatter: function (val) { return 'MWK ' + val + 'M'; }, style: { colors: '#7890a8', fontSize: '9px' } }
            },
            grid: { borderColor: '#edf2f7', strokeDashArray: 3, padding: { left: 8, right: 8 } },
            legend: { show: false },
            tooltip: { y: { formatter: function (val) { return 'MWK ' + val + 'M'; } } }
        }).render();
    }

    // --- Trip status (donut) ---------------------------------------------------
    var statusElement = document.querySelector('#tripStatusChart');
    if (statusElement && window.ApexCharts) {
        new ApexCharts(statusElement, {
            chart: { type: 'donut', height: 190, fontFamily: 'inherit' },
            series: data.trip_status_values || [0, 0, 0, 0, 0],
            labels: data.trip_status_labels || ['In Transit', 'Loading', 'At Border', 'Delivered', 'Delayed'],
            colors: ['#16b981', '#f59e0b', '#1688e8', '#079ca4', '#ef4444'],
            stroke: { width: 2, colors: ['#fff'] },
            dataLabels: {
                enabled: true,
                formatter: function (val) { return Math.round(val) + '%'; },
                style: { fontSize: '10px', fontWeight: 700 }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '63%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '10px', color: '#7890a8', offsetY: -4 },
                            value: {
                                show: true,
                                fontSize: '20px',
                                fontWeight: 800,
                                color: '#102e50',
                                offsetY: 3,
                                formatter: function (val) { return val; }
                            },
                            total: {
                                show: true,
                                label: 'Total Trips',
                                color: '#7890a8',
                                fontSize: '10px',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0);
                                }
                            }
                        }
                    }
                }
            },
            legend: { show: false },
            tooltip: { y: { formatter: function (val) { return val + ' trips'; } } }
        }).render();
    }

    // --- Mobile menu toggle ----------------------------------------------------
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
})();
