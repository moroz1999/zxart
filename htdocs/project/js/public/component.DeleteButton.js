window.DeleteButtonComponent = function(componentElement) {
	var init = function() {
		eventsManager.addHandler(componentElement, 'click', clickHandler);
	};
	var clickHandler = function(event) {
		if (!confirm(translationsLogics.get('controls.delete_confirmation'))) {
			eventsManager.preventDefaultAction(event);
		}
	};
	init();
};