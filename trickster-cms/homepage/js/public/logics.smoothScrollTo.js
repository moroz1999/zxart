window.SmoothScrollTo = new function() {
	var initComponents = function() {
		var elements = _('.smooth_scroll_to');
		for (var i = 0; i < elements.length; i++) {
			new SmoothScrollToComponent(elements[i]);
		}
	};
	controller.addListener('initDom', initComponents);
};