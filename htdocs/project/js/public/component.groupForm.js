window.GroupFormComponent = function(componentElement) {
	var init = function() {
		var citySelectElements = _('.group_form_city_select', componentElement);
		for (var i = citySelectElements.length; i--;) {
			new AjaxSelectComponent(citySelectElements[i], 'city');
		}
		var countrySelectElement = _('.group_form_country_select', componentElement);
		for (var i = countrySelectElement.length; i--;) {
			new AjaxSelectComponent(countrySelectElement[i], 'country');
		}
		var groupsSelectElement = _('.group_form_subgroup_select', componentElement);
		for (var i = groupsSelectElement.length; i--;) {
			new AjaxSelectComponent(groupsSelectElement[i], 'group');
		}
		var authorSelectElement = _('.author_form_select', componentElement);
		for (var i = authorSelectElement.length; i--;) {
			new AjaxSelectComponent(authorSelectElement[i], 'author,authorAlias');
		}
		var groupAsAliasJoinSelectElement = _('.group_form_joinasalias_select', componentElement);
		for (i = groupAsAliasJoinSelectElement.length; i--;) {
			new AjaxSelectComponent(groupAsAliasJoinSelectElement[i], 'group', 'public');
		}
		var groupJoinAndDeleteSelectElement = _('.group_form_joinanddelete_select', componentElement);
		for (i = groupJoinAndDeleteSelectElement.length; i--;) {
			new AjaxSelectComponent(groupJoinAndDeleteSelectElement[i], 'group,groupAlias', 'public');
		}

	};
	init();
};