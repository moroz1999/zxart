window.ChartComponent = function(componentElement) {
    var id;
    var data;
    var init = function() {
        if (id = componentElement.dataset.chartid) {
            if (data = chartLogics.getChartData(id)) {
                if (componentElement.dataset.charttype == 'bar') {
                    buildBarChart(componentElement, data);
                } else {
                    buildLineChart(componentElement, data);
                }
            }
        }
    };
    var buildLineChart = function(element, data) {
        var chartData = {
            labels: data.labels,
            datasets: [
                {
                    responsive: true,
                    label: data.label,
                    borderColor: '#5eadf0',
                    // backgroundColor: "rgba(151,187,205,0.5)",
                    data: data.data,
                },
            ],
        };

        if (data.additionalDatasets) {
            for (var i = 0; i < data.additionalDatasets.length; i++) {
                chartData.datasets.push({
                    responsive: true,
                    label: data.additionalDatasets[i].label,
                    borderColor: data.additionalDatasets[i].borderColor,
                    backgroundColor: data.additionalDatasets[i].backgroundColor,
                    data: data.additionalDatasets[i].data,
                });
            }
        }

        var config = {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                scales: {
                    yAxes: [
                        {
                            stacked: true,
                        },
                    ],
                },
                legend: {
                    display: false,
                },
                tooltips: {
                    mode: 'index',
                },
            },
        };

        if (componentElement.className.indexOf('container_height_depends') != -1) {
            config.options.maintainAspectRatio = false;
        }

        if (componentElement.dataset.currency) {
            config.options.tooltips.callbacks = {
                label: currencyTooltips,
            };
        }

        new Chart(element.getContext('2d'), config);
    };

    var buildBarChart = function(element, data) {
        var barChartData = {
            labels: data.labels,
            datasets: [
                {
                    label: data.label,
                    backgroundColor: data.fillColor,
                    borderColor: data.fillColor,
                    data: data.data,
                },
            ],
        };

        var config = {
            type: 'bar',
            data: barChartData,
            options: {
                legend: {
                    display: false,
                },
                scales: {
                    yAxes: [
                        {
                            ticks: {
                                beginAtZero: true,
                            },
                        },
                    ],
                },
            },
        };

        if (componentElement.dataset.currency) {
            config.options.tooltips = {
                callbacks: {
                    label: currencyTooltips,
                },
            };
        }

        var barChart = new Chart(element.getContext('2d'), config);
    };

    var currencyTooltips = function(tooltipItem, data) {
        var output = data.datasets[tooltipItem.datasetIndex].label + ': ';
        if (tooltipItem.yLabel) {
            output += tooltipItem.yLabel.toFixed(2) + ' ' + componentElement.dataset.currency;
        } else {
            output += '0';
        }
        return output;
    };

    init();
};