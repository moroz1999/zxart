window.ZxItemFormComponent = function(componentElement) {
	var init = function() {
		var i;
		var authorsSelectElements = _('.zxitem_form_authors_select', componentElement);
		for (i = authorsSelectElements.length; i--;) {
			new AjaxSelectComponent(authorsSelectElements[i], 'author,authorAlias', 'admin');
		}
		var gameSelectElement = _('.zxitem_form_prodrelease_select', componentElement);
		for (i = gameSelectElement.length; i--;) {
			new AjaxSelectComponent(gameSelectElement[i], 'game', 'admin');
		}
		var partySelectElement = _('.zxitem_form_party_select', componentElement);
		for (i = gameSelectElement.length; i--;) {
			new AjaxSelectComponent(partySelectElement[i], 'party', 'admin');
		}
	};
	init();
};