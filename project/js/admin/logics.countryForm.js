window.countryFormLogics = new function() {
	var initComponents = function() {
		var elements = _('.country_form');
		for (var i = elements.length; i--;) {
			new CountryFormComponent(elements[i]);
		}
	};
	window.controller.addListener('initDom', initComponents);
};