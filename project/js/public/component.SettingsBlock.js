window.SettingsBlockComponent = function(componentElement) {
	var init = function() {
		var elements, i;
		elements = _('.button', componentElement);
		for (i = elements.length; i--;) {
			new SettingsBlockInput(elements[i]);
		}
	};
	init();
};
window.SettingsBlockInput = function(componentElement) {
	var init = function() {
		componentElement.addEventListener('click', clickHandler);
	};
	var clickHandler = function() {
		window.location.href = window.currentElementURL + componentElement.dataset.operation + '/';
	};
	init();
};
