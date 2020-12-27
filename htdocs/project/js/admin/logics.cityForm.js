window.cityFormLogics = new function() {
	var initComponents = function() {
		var elements = _('.city_form');
		for (var i = elements.length; i--;) {
			new CityFormComponent(elements[i]);
		}
	};
	window.controller.addListener('initDom', initComponents);
};