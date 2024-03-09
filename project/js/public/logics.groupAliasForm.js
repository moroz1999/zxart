window.groupAliasFormLogics = new function() {
	var initComponents = function() {
		var elements = _('.groupalias_form');
		for (var i = elements.length; i--;) {
			new GroupAliasFormComponent(elements[i]);
		}
	};
	window.controller.addListener('initDom', initComponents);
};