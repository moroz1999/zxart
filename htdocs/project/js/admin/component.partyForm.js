window.PartyFormComponent = function(componentElement) {
	var init = function() {
		var citySelectElements = _('.party_form_city_select', componentElement);
		for (var i = citySelectElements.length; i--;) {
			new AjaxSelectComponent(citySelectElements[i], 'city', 'admin');
		}
		var countrySelectElement = _('.party_form_country_select', componentElement);
		for (var i = countrySelectElement.length; i--;) {
			new AjaxSelectComponent(countrySelectElement[i], 'country', 'admin');
		}
	};
	init();
};