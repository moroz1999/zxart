window.detailedSearchLogics = new function() {
	var initComponents = function() {
		var elements = _('.detailedsearch_block');
		for (var i = 0; i < elements.length; i++) {
			new DetailedSearchFormComponent(elements[i]);
		}
	};
	window.controller.addListener('initDom', initComponents);
};