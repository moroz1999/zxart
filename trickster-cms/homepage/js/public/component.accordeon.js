window.Accordeon = function(componentElement) {
	var mode = 'hover';
	var items = [];
	var self = this;
	var init = function() {
		if (componentElement.dataset.accordeonMode) {
			mode = componentElement.dataset.accordeonMode;
		}
		var elements = componentElement.querySelectorAll('.accordeon_item');
		for (var i = 0; i < elements.length; i++) {
			items.push(new AccordeonItem(elements[i], self, mode));
		}
		items[0].open();
	};
	this.openItem = function(newItem) {
		for (var i = 0; i < items.length; i++) {
			if (items[i] === newItem) {
				items[i].open();
			} else {
				items[i].close();
			}
		}
	};
	init();
};

window.AccordeonItem = function(componentElement, parentComponent, mode) {
	var activeClassName = 'accordeon_item_active';
	var titleElement = false;
	var contentElement = false;
	var opened = false;
	var self = this;
	var wrapperComponent;

	var init = function() {
		if (titleElement = componentElement.querySelector('.accordeon_item_title')) {
			wrapperComponent = componentElement.querySelector('.subarticle_accordeon_content_wrapper');
			if (contentElement = componentElement.querySelector('.accordeon_item_content')) {
				if (mode === 'hover') {
					eventsManager.addHandler(componentElement, 'mouseenter', interactionHandler);
				} else if (mode === 'click') {
					eventsManager.addHandler(titleElement, 'click', interactionHandler);
				}
			}
		}
		if (componentElement.dataset.opened) {
			opened = true;
			self.open(true);
		}
	};
	var interactionHandler = function() {
		if(opened) {
			self.close();
		} else {
			parentComponent.openItem(self);
		}
	};
	this.open = function(instant) {
		if (!opened) {
			opened = true;
			var height = contentElement.scrollHeight;
			if (instant) {
				componentElement.classList.add(activeClassName);
				wrapperComponent.style.height = height + 'px';
			} else {
				componentElement.classList.add(activeClassName);
				TweenLite.to(wrapperComponent, 0.5, {'css': {
					'height': height}});
			}
		}
	};
	this.close = function() {
		if (opened) {
			opened = false;
			TweenLite.to(wrapperComponent, 0.3, {'css': {'height': 0}});
			componentElement.classList.remove(activeClassName);

		}
	};
	init();
};