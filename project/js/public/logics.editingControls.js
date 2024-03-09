window.editingControlsLogics = new function() {
	var initComponents = function() {
		var elements = _('.editing_controls');
		for (var i = elements.length; i--;) {
			new EditingControlsComponent(elements[i]);
		}
	};
	window.controller.addListener('initDom', initComponents);
};