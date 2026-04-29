window.chartLogics = new function() {
    var components = [];
    var chartsData;
    var initComponents = function() {
        if (typeof Chart !== 'undefined') {
            Chart.defaults.global.responsive = true;
            var elements = _('.chart_component');
            for (var i = 0; i < elements.length; i++) {
                components.push(new ChartComponent(elements[i]));
            }
        }
    };
    var initLogics = function() {
        if (typeof window.chartsData !== 'undefined') {
            chartsData = window.chartsData;
        }
        if (typeof Chart !== 'undefined') {
            Chart.defaults.global.scaleBeginAtZero = true;
            Chart.defaults.global.responsive = true;
            Chart.defaults.global.animation = false;
        }
    };
    this.getChartData = function(id) {
        if (typeof chartsData[id] !== 'undefined') {
            return chartsData[id];
        }
        return false;
    };
    controller.addListener('initLogics', initLogics);
    controller.addListener('initDom', initComponents);
};