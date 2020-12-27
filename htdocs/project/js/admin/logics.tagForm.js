window.tagFormLogics = new function() {
	var initComponents = function() {
		var elements = _('.jointag_form');
		for (var i = 0; i < elements.length; i++) {
			new JoinTagFormComponent(elements[i]);
		}
	};
	window.controller.addListener('initDom', initComponents);
};