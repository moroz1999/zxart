window.AuthorFormComponent = function(componentElement) {
	var init = function() {
		var i;
		var citySelectElements = _('.author_form_city_select', componentElement);
		for (i = citySelectElements.length; i--;) {
			new AjaxSelectComponent(citySelectElements[i], 'city', 'public');
		}
		var countrySelectElement = _('.author_form_country_select', componentElement);
		for (i = countrySelectElement.length; i--;) {
			new AjaxSelectComponent(countrySelectElement[i], 'country', 'public');
		}
		var authorAsAliasJoinSelectElement = _('.author_form_joinasalias_select', componentElement);
		for (i = authorAsAliasJoinSelectElement.length; i--;) {
			new AjaxSelectComponent(authorAsAliasJoinSelectElement[i], 'author', 'public');
		}
		var authorJoinAndDeleteSelectElement = _('.author_form_joinanddelete_select', componentElement);
		for (i = authorJoinAndDeleteSelectElement.length; i--;) {
			new AjaxSelectComponent(authorJoinAndDeleteSelectElement[i], 'author,authorAlias', 'public');
		}
	};
	init();
};