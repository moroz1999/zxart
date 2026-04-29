window.SmoothScrollToComponent = function(componentElement) {
	var toElement;

	var addEvent = function() {
		eventsManager.addHandler(componentElement, 'click', clickHandler);
	};
	var clickHandler = function(event) {
		eventsManager.preventDefaultAction(event);
		TweenLite.to(window, 1, {scrollTo: {y: toElement.offsetTop, autoKill: false}, ease: Power2.easeOut});
	};
	var init = function() {
		if (componentElement.dataset.scrollTo) {
			toElement = document.querySelector(componentElement.dataset.scrollTo);
			if (toElement) {
				addEvent();
			}
		}
	};

	init();
};