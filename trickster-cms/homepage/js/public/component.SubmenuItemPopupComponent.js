window.SubmenuItemPopupComponent = function(subMenusList, menuComponent, referenceElement) {
	var self = this;
	var componentElement;
	var contentElement;
	var displayed = false;
	var hideTimeout = false;
	var hovered = false;
	var attached = false;

	this.componentElement = false;

	var createDomStructure = function() {
		componentElement = document.createElement('div');

		componentElement.className = 'submenuitem_popup_block';
		componentElement.style.opacity = '0';

		contentElement = document.createElement('div');
		contentElement.className = 'submenuitem_popup_content';
		componentElement.appendChild(contentElement);
		for (var i = 0; i < subMenusList.length; i++) {
			var subMenu = new SubMenusPopupItemComponent(subMenusList[i]);
			contentElement.appendChild(subMenu.componentElement);
		}
		self.componentElement = componentElement;
		window.eventsManager.addHandler(componentElement, 'mouseenter', onMouseEnter);
		window.eventsManager.addHandler(componentElement, 'mouseleave', onMouseLeave);
	};
	var attach = function() {
		if (!attached) {
			if (!componentElement) {
				createDomStructure();
			}
			attached = true;
			document.body.appendChild(componentElement);
		}
	};
	var detach = function() {
		if (attached) {
			attached = false;
			document.body.removeChild(componentElement);
		}
	};
	this.displayComponent = function() {
		window.clearTimeout(hideTimeout);
		var positions;
		if (!displayed) {
			displayed = true;
			attach();
			if (menuComponent.isVerticalPopup()) {
				var menuWidth = referenceElement.offsetWidth;
				var menuHeight = referenceElement.offsetHeight;

				componentElement.style.display = 'block';
				if (componentElement.offsetWidth < menuWidth) {
					componentElement.style.minWidth = menuWidth + 'px';
				}

				positions = window.domHelper.getElementPositions(referenceElement);
				componentElement.style.left = positions.x + 'px';
				componentElement.style.top = (positions.y + menuHeight) + 'px';

				TweenLite.to(componentElement, 0.25, {'css': {'opacity': 1}});
			} else {
				componentElement.style.display = 'block';
				positions = window.domHelper.getElementPositions(referenceElement);

				componentElement.style.left = positions.x + referenceElement.offsetWidth + 'px';
				componentElement.style.top = positions.y + 'px';

				TweenLite.to(componentElement, 0.25, {'css': {'opacity': 1}});
			}
		}
	};
	this.attemptHideComponent = function() {
		hideTimeout = window.setTimeout(startHideComponent, 250);
	};
	var startHideComponent = function() {
		if (!hovered) {
			displayed = false;
			if (componentElement) {
				TweenLite.to(componentElement, 0.1, {'css': {'opacity': 0}, 'onComplete': hideComponent});
			}
		}
	};
	var hideComponent = function() {
		if (!displayed) {
			if (componentElement) {
				componentElement.style.display = 'none';
				detach();
			}
		}
	};

	var onMouseEnter = function(event) {
		hovered = true;
		menuComponent.mouseEnterHandler(event);
	};
	var onMouseLeave = function(event) {
		hovered = false;
		menuComponent.mouseLeaveHandler(event);
	};
};