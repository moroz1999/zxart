window.zxItemFormLogics = new function() {
	var initComponents = function() {
		var elements = _('.zxitem_form');
		for (var i = elements.length; i--;) {
			new ZxItemFormComponent(elements[i]);
		}
	};
	window.controller.addListener('initDom', initComponents);
};