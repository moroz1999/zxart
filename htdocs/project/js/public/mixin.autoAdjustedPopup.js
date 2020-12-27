window.AutoAdjustedPopup = function() {
	var componentElement;
	var self = this;
	this.initAutoAdjustedPopup = function(options) {
		parseOptions(options);
		if (componentElement) {
		}
	};
	var parseOptions = function(options) {
		if (typeof options.componentElement != 'undefined') {
			componentElement = options.componentElement;
		}
	};
	this.adjustPositionAndSize = function(referenceElement) {
		componentElement.style.visibility = 'hidden';
		componentElement.style.display = 'block';

		var x;
		var y;

		var componentWidth = componentElement.offsetWidth;
		var componentHeight = componentElement.offsetHeight;

		var scroll = self.getPageScroll();

		var windowHeight = self.getWindowHeight();
		var windowWidth = self.getWindowWidth();

		var rect = referenceElement.getBoundingClientRect();
		if (rect.left > windowWidth / 2) {
			x = rect.left - componentWidth + referenceElement.offsetWidth + scroll.x;
		}
		else {
			x = rect.left + scroll.x;
		}
		if (rect.top > windowHeight / 2) {
			y = rect.top - componentHeight + referenceElement.offsetHeight + scroll.y;
		}
		else {
			y = rect.top + scroll.y;
		}

		componentElement.style.left = x + 'px';
		componentElement.style.top = y + 'px';

		componentElement.style.display = 'none';
		componentElement.style.visibility = 'visible';
	};

	return this;
};