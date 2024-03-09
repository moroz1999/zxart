window.AuthorFormComponent = function(componentElement) {
	var init = function() {
		var i;
		var citySelectElements = _('.author_form_city_select', componentElement);
		for (i = citySelectElements.length; i--;) {
			new AjaxSelectComponent(citySelectElements[i], 'city', 'admin');
		}
		var countrySelectElement = _('.author_form_country_select', componentElement);
		for (i = countrySelectElement.length; i--;) {
			new AjaxSelectComponent(countrySelectElement[i], 'country', 'admin');
		}
		var authorSelectElement = _('.author_form_join_select', componentElement);
		for (i = authorSelectElement.length; i--;) {
			new AjaxSelectComponent(authorSelectElement[i], 'author', 'admin');
		}
	};
	init();
};