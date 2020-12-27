window.EditingControlsComponent = function(componentElement) {
	var init = function() {
		var elements, i;
		elements = _('.button', componentElement);
		for (i = elements.length; i--;) {
			new EditingControlsButtonComponent(elements[i]);
		}
		elements = _('.delete_button', componentElement);
		for (i = elements.length; i--;) {
			new DeleteButtonComponent(elements[i]);
		}
		elements = _('.convert_button', componentElement);
		for (i = elements.length; i--;) {
			new ConvertButtonComponent(elements[i]);
		}
	};
	init();
};

window.EditingControlsButtonComponent = function(componentElement) {
	var init = function() {
		if (window.userName == 'anonymous') {
			var popup = new TipPopupComponent(componentElement, window.translationsLogics.get('controls.registration_required'));
			popup.setDisplayDelay(100);
			eventsManager.addHandler(componentElement, 'click', clickHandler);
		}
	};
	var clickHandler = function(event) {
		eventsManager.preventDefaultAction(event);
	};
	init();
};

window.ConvertButtonComponent = function(componentElement) {
	var init = function() {
		eventsManager.addHandler(componentElement, 'click', clickHandler);
	};
	var clickHandler = function(event) {
		if (!confirm(translationsLogics.get('controls.convert_confirmation'))) {
			eventsManager.preventDefaultAction(event);
		}
	};
	init();
};