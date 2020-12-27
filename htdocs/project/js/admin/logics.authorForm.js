window.authorFormLogics = new function() {
	var initComponents = function() {
		var elements = _('.author_form');
		for (var i = elements.length; i--;) {
			new AuthorFormComponent(elements[i]);
		}
	};
	window.controller.addListener('initDom', initComponents);
};