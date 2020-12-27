window.radioControlsLogics = new function() {
	var mode;
	var initComponents = function() {
		var elements = document.querySelectorAll('.radio_controls');
		for (var i = 0; i < elements.length; i++) {
			new RadioControlsComponent(elements[i]);
		}
	};
	this.startPlay = function(newMode) {
		mode = newMode;
		musicRadioLogics.startPlay(mode);
	};

	window.controller.addListener('initDom', initComponents);
};
window.RadioControlsComponent = function(componentElement) {
	var init = function() {
		var buttons = componentElement.querySelectorAll('.button');
		if (buttons) {
			for (var i = 0; i < buttons.length; i++) {
				eventsManager.addHandler(buttons[i], 'click', clickHandler);
			}
		}
	};
	var clickHandler = function(event) {
		var type = event.currentTarget.getAttribute('data-radiotype');
		radioControlsLogics.startPlay(type);
	};

	init();
};