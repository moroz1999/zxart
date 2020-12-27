window.CityFormComponent = function(componentElement) {
	var init = function() {
		var citySelectElement = _('.city_form_jointag_select', componentElement);
		for (var i = citySelectElement.length; i--;) {
			new AjaxSelectComponent(citySelectElement[i], 'city', 'admin');
		}
	};
	init();
};