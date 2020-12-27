window.GroupAliasFormComponent = function(componentElement) {
	var init = function() {
		var groupSelectElements = _('.groupalias_form_group_select', componentElement);
		for (var i = groupSelectElements.length; i--;) {
			new AjaxSelectComponent(groupSelectElements[i], 'group', 'public');
		}
		var authorSelectElement = _('.author_form_select', componentElement);
		for (var i = authorSelectElement.length; i--;) {
			new AjaxSelectComponent(authorSelectElement[i], 'author,authorAlias');
		}
	};
	init();
};