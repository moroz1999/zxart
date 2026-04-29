//requires DomHelperMixin to be used

window.ScrollAttachingMixin = function() {
	var componentElement, placeHolderElement;
	var previousPageScrollY;
	var style;
	var self = this;
	var calculatedTop = 0;
	var defaultPosition = '';
	var originalTopOffsetHeight = 0;
	var width = false;
	var isMobile = false;
	this.activated = false;

	this.initScrollAttaching = function(options) {
		if (options.componentElement) {
			componentElement = options.componentElement;
		}
		if (options.defaultPosition) {
			defaultPosition = options.defaultPosition;
		}
		if (componentElement) {

			if (options.autoAdjustWidth) {
				width = componentElement.offsetWidth;
			}
			if (options.topOffsetHeight) {
				originalTopOffsetHeight = options.topOffsetHeight;
			}

			if (options.usePlaceHolder) {
				placeHolderElement = document.createElement(componentElement.tagName);
				placeHolderElement.className = componentElement.className;
				if (!options.displayPlaceholder) {
					placeHolderElement.style.display = 'none';
				}
				componentElement.parentNode.insertBefore(placeHolderElement, componentElement);
			}
			style = componentElement.style;
			previousPageScrollY = self.getPageScroll().y;
			self.scrollAttachActivated();
		}
	};

	this.adjustPosition = function() {
		var direction;

		var pageScrollY = self.getPageScroll().y;
		if (pageScrollY > previousPageScrollY) {
			direction = 'down';
		} else {
			direction = 'up'
		}
		var parentElement = componentElement.parentNode;
		var componentPosition = self.getPosition(componentElement);
		var parentHeight = parentElement.offsetHeight;
		var componentHeight = componentElement.offsetHeight;
		var componentBottom = componentPosition.y + componentElement.offsetHeight;
		var componentTop = componentPosition.y;
		var topBoundary = self.getPosition(parentElement).y;
		var viewPortHeight = self.getWindowHeight();
		var pageScrollBottomY = pageScrollY + viewPortHeight;
		var bottomBoundary = topBoundary + parentHeight;

		if (width) {
			componentElement.style.width = width + 'px';
		}

		//by default, placeholder is displayed, current position is absolute
		var displayPlaceholder = '';
		var currentPosition = 'absolute';

		//is element higher than screen, so inside-scrolling is required?
		if (componentHeight > viewPortHeight) {
			//yes, element is higher than the screen.
			//did we just scroll down or up?
			if (direction == 'down') {
				//we scrolled down.
				//have we scrolled lower than elements container?
				if (pageScrollBottomY < bottomBoundary) {

					//no, we are just in scrolling process
					//should we attach bottom of our element to screen's bottom
					if (pageScrollBottomY > componentBottom) {
						//yes, we should attach bottom of our element to screen's bottom
						calculatedTop = pageScrollBottomY - (componentHeight + topBoundary);
					}
				} else {
					//we have scrolled lower than elements container,
					//it should be positioned absolutely at bottom and not move
					calculatedTop = bottomBoundary - (componentHeight + topBoundary);
				}
			}
			else if (direction == 'up') {
				//we scrolled up.
				//have we scrolled upper than elements container?
				if (pageScrollY + originalTopOffsetHeight > topBoundary) {
					//no, we are just in scrolling process
					//should we attach top of our element to screen's top?
					if (pageScrollY + originalTopOffsetHeight < componentTop) {
						//yes, we should attach top of our element to screen's top
						calculatedTop = pageScrollY + originalTopOffsetHeight - topBoundary;
					}
				} else {
					//element should be displayed on original place, on top
					calculatedTop = 0;
					//use default element positioning, hide placeholder
					currentPosition = defaultPosition;
					displayPlaceholder = 'none';
				}
			}
		} else {
			//element fits into screen height.
			//is page scrolled down below original element's position?
			if (pageScrollY + originalTopOffsetHeight > topBoundary) {
				//yes, page is scrolled down, we should reposition element
				//is element at the bottom of it's parent?
				if (pageScrollY - topBoundary + componentHeight > parentHeight) {
					//element is at the bottom of its parent, shouldn't be attached to screen anymore
					calculatedTop = parentHeight - componentHeight;
				} else {
					//element is attached to screen, it's neither on top nor on bottom
					calculatedTop = originalTopOffsetHeight;
					currentPosition = 'fixed';
				}
			} else {
				//element should be displayed on original place, on top
				calculatedTop = 0;
				//use default element positioning, hide placeholder
				currentPosition = defaultPosition;
				displayPlaceholder = 'none';
			}
		}

		if (placeHolderElement) {
			placeHolderElement.style.height = componentElement.offsetHeight + 'px';
			placeHolderElement.style.display = displayPlaceholder;
		}

		style.position = currentPosition;
		style.top = calculatedTop + 'px';
		previousPageScrollY = pageScrollY;
	};

	this.onScroll = function() {
		if(self.activated) {
			self.adjustPosition();
		}
	};

	this.onResize = function() {
		if(self.activated) {
			self.adjustPosition();
		}
	};

	this.scrollAttachActivated = function() {
		if(!self.activated) {
			self.activated = true;
			eventsManager.addHandler(window, "resize", self.onResize);
			eventsManager.addHandler(window, "scroll", self.onScroll);
		}
	};

	this.scrollAttachDiactiveted = function() {
		if(self.activated) {
			self.activated = false;
			eventsManager.removeHandler(window, "resize", self.onResize);
			eventsManager.removeHandler(window, "scroll", self.onScroll);
		}
	};
};