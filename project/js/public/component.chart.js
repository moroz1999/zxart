window.ChartComponent = function(componentElement) {
	var id;
	var data;
	var init = function() {
		if (id = componentElement.dataset.chartid) {
			if (data = chartLogics.getChartData(id)) {
				buildChart(componentElement, data);
			}
		}
	};
	var buildChart = function(element, data) {
		var chartData = {
			labels: data.labels,
			datasets: [
				{
					label: "",
					fillColor: "rgba(151,187,205,0.2)",
					strokeColor: "rgba(151,187,205,1)",
					pointColor: "rgba(151,187,205,1)",
					pointStrokeColor: "#fff",
					pointHighlightFill: "#fff",
					pointHighlightStroke: "rgba(151,187,205,1)",
					data: data.data
				}
			]
		};
		var config = {
			type: 'line',
			data: chartData,
			options: {
				responsive: true,
				scales: {
					yAxes: [{
						stacked: true
					}]
				},
				legend: {
					display: false
				},
				tooltips: {
					mode: 'index'
				}
			}
		};
		new Chart(element.getContext("2d"), config);
	};

	init();
};