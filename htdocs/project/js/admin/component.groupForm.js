window.GroupFormComponent = function(componentElement) {
	var init = function() {
		var citySelectElements = _('.group_form_city_select', componentElement);
		for (var i = citySelectElements.length; i--;) {
			new AjaxSelectComponent(citySelectElements[i], 'city', 'admin');
		}
		var countrySelectElement = _('.group_form_country_select', componentElement);
		for (var i = countrySelectElement.length; i--;) {
			new AjaxSelectComponent(countrySelectElement[i], 'country', 'admin');
		}
	};
	init();
};