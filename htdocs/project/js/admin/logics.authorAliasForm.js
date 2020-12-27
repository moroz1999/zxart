window.authorAliasFormLogics = new function() {
	var initComponents = function() {
		var elements = _('.authoralias_form');
		for (var i = elements.length; i--;) {
			new AuthorAliasFormComponent(elements[i]);
		}
	};
	window.controller.addListener('initDom', initComponents);
};