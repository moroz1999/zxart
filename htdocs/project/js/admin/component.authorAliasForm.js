window.AuthorAliasFormComponent = function(componentElement) {
	var init = function() {
		var authorSelectElements = _('.authoralias_form_author_select', componentElement);
		for (var i = authorSelectElements.length; i--;) {
			new AjaxSelectComponent(authorSelectElements[i], 'author', 'public');
		}
	};
	init();
};