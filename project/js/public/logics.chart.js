window.chartLogics = new function() {
	var components = [];
	var chartsData;
	var initComponents = function() {
		if (typeof Chart !== 'undefined'){
			Chart.defaults.global.scaleBeginAtZero = true;
			Chart.defaults.global.responsive = false;
			Chart.defaults.global.animation = false;
		
			var elements = _('.chart_component');
			for (var i = 0; i < elements.length; i++) {
				components.push(new ChartComponent(elements[i]));
			}
		}
	};
	var initLogics = function() {
		if (window.chartsData != undefined) {
			chartsData = window.chartsData;
		}		
	};
	this.getChartData = function(id) {
		if (typeof chartsData[id] != "undefined") {
			return chartsData[id];
		}
		return false;
	};
	window.controller.addListener('initLogics', initLogics);
	window.controller.addListener('initDom', initComponents);
};