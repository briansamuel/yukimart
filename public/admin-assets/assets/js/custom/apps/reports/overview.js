"use strict";

// Class definition
var KTReportsOverview = function () {
    // Private variables
    var revenueChart;
    var ordersChart;
    var comparisonChart;

    // Private functions
    var initRevenueChart = function () {
        var element = document.getElementById('kt_charts_widget_1_chart');
        
        if (!element) {
            return;
        }

        var options = {
            series: [{
                name: 'Doanh thu',
                data: []
            }],
            chart: {
                fontFamily: 'inherit',
                type: 'area',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {},
            legend: {
                show: false
            },
            dataLabels: {
                enabled: false
            },
            fill: {
                type: 'solid',
                opacity: 1
            },
            stroke: {
                curve: 'smooth',
                show: true,
                width: 3,
                colors: ['#5e72e4']
            },
            xaxis: {
                categories: [],
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: '#8c9097',
                        fontSize: '13px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#8c9097',
                        fontSize: '13px'
                    },
                    formatter: function (val) {
                        return new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND'
                        }).format(val);
                    }
                }
            },
            states: {
                normal: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                hover: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'none',
                        value: 0
                    }
                }
            },
            tooltip: {
                style: {
                    fontSize: '12px'
                },
                y: {
                    formatter: function (val) {
                        return new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND'
                        }).format(val);
                    }
                }
            },
            colors: ['#5e72e4'],
            grid: {
                borderColor: '#e7eaf3',
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            markers: {
                strokeColor: '#5e72e4',
                strokeWidth: 3
            }
        };

        revenueChart = new ApexCharts(element, options);
        revenueChart.render();
    };

    // Initialize orders chart
    var initOrdersChart = function () {
        var element = document.getElementById('kt_charts_widget_2_chart');
        
        if (!element) {
            return;
        }

        var options = {
            series: [],
            chart: {
                fontFamily: 'inherit',
                type: 'donut',
                width: 350,
                height: 350
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '50%'
                    }
                }
            },
            colors: ['#00D4AA', '#FFB800', '#F64E60', '#8950FC'],
            stroke: {
                width: 0
            },
            labels: [],
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center',
                labels: {
                    colors: '#8c9097',
                    useSeriesColors: false
                },
                markers: {
                    width: 10,
                    height: 10,
                    strokeWidth: 0,
                    strokeColor: '#fff',
                    fillColors: undefined,
                    radius: 6,
                    customHTML: undefined,
                    onClick: undefined,
                    offsetX: 0,
                    offsetY: 0
                },
                itemMargin: {
                    horizontal: 15,
                    vertical: 8
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        ordersChart = new ApexCharts(element, options);
        ordersChart.render();
    };

    // Initialize comparison chart
    var initComparisonChart = function () {
        var element = document.getElementById('kt_charts_widget_3_chart');
        
        if (!element) {
            return;
        }

        var options = {
            series: [{
                name: 'Kỳ hiện tại',
                data: []
            }, {
                name: 'Kỳ trước',
                data: []
            }],
            chart: {
                fontFamily: 'inherit',
                type: 'bar',
                height: 400,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '30%',
                    endingShape: 'rounded'
                },
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'left',
                labels: {
                    colors: '#8c9097',
                    useSeriesColors: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: [],
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: '#8c9097',
                        fontSize: '13px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#8c9097',
                        fontSize: '13px'
                    },
                    formatter: function (val) {
                        return new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND'
                        }).format(val);
                    }
                }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND'
                        }).format(val);
                    }
                }
            },
            colors: ['#5e72e4', '#00D4AA'],
            grid: {
                borderColor: '#e7eaf3',
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            }
        };

        comparisonChart = new ApexCharts(element, options);
        comparisonChart.render();
    };

    // Load statistics
    var loadStatistics = function() {
        fetch('/admin/reports/statistics')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('total_revenue').textContent = 
                        new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(data.data.total_revenue);
                    document.getElementById('total_orders').textContent = data.data.total_orders;
                    document.getElementById('total_customers').textContent = data.data.total_customers;
                    document.getElementById('total_products').textContent = data.data.total_products;
                }
            })
            .catch(error => console.error('Error loading statistics:', error));
    };

    // Load revenue chart data
    var loadRevenueChart = function(period = 30) {
        fetch(`/admin/reports/revenue-chart?period=${period}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && revenueChart) {
                    revenueChart.updateOptions({
                        xaxis: {
                            categories: data.data.labels
                        }
                    });
                    revenueChart.updateSeries([{
                        name: 'Doanh thu',
                        data: data.data.values
                    }]);
                }
            })
            .catch(error => console.error('Error loading revenue chart:', error));
    };

    // Load orders chart data
    var loadOrdersChart = function() {
        fetch('/admin/reports/orders-chart')
            .then(response => response.json())
            .then(data => {
                if (data.success && ordersChart) {
                    ordersChart.updateOptions({
                        labels: data.data.labels
                    });
                    ordersChart.updateSeries(data.data.values);
                }
            })
            .catch(error => console.error('Error loading orders chart:', error));
    };

    // Public methods
    return {
        init: function () {
            initRevenueChart();
            initOrdersChart();
            initComparisonChart();
            loadStatistics();
            loadRevenueChart();
            loadOrdersChart();

            // Event handlers
            document.getElementById('revenue_chart_period')?.addEventListener('change', function(e) {
                loadRevenueChart(e.target.value);
            });
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTReportsOverview.init();
});
