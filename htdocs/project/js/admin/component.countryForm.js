window.CountryFormComponent = function(componentElement) {
	var init = function() {
		var countrySelectElement = _('.country_form_jointag_select', componentElement);
		for (var i = countrySelectElement.length; i--;) {
			new AjaxSelectComponent(countrySelectElement[i], 'country', 'admin');
		}
	};
	init();
};