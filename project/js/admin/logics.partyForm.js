window.partyFormLogics = new function() {
	var initComponents = function() {
		var elements = _('.party_form');
		for (var i = elements.length; i--;) {
			new PartyFormComponent(elements[i]);
		}
	};
	window.controller.addListener('initDom', initComponents);
};