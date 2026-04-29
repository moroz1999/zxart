window.SubMenuItemComponent = function(componentElement) {
	var self = this;
	var verticalPopup = false;
	var menuInfo = false;
	var popupObject = false;
	this.id = false;
	var displayDelayTimeout;

	var init = function() {
		self.id = parseInt(componentElement.className.split('menuid_')[1], 10);
		if (componentElement.className.indexOf('vertical_popup') >= 0) {
			verticalPopup = true;
		}
		if (menuInfo = window.subMenuLogics.getMenuInfo(self.id)) {
			var subMenuList = window.subMenuLogics.getSubMenuInfo(self.id);
			if (subMenuList.length > 0) {

				popupObject = new SubmenuItemPopupComponent(subMenuList, self, componentElement);
			}
			window.eventsManager.addHandler(componentElement, 'mouseenter', self.mouseEnterHandler);
			window.eventsManager.addHandler(componentElement, 'mouseleave', self.mouseLeaveHandler);
		}
	};

	this.mouseEnterHandler = function() {
		domHelper.addClass(componentElement, 'menuitem_hover');
		if (popupObject) {
			displayDelayTimeout = window.setTimeout(function() {
				popupObject.displayComponent();
			}, 150);
		}
	};

	this.mouseLeaveHandler = function(event) {
		window.clearTimeout(displayDelayTimeout);

		if (popupObject) {
			var hidingRequired = true;

			if (typeof event.relatedTarget == 'undefined' && typeof event.toElement == 'object') {
				event.relatedTarget = event.toElement;
			}

			if (event.relatedTarget) {
				if (event.relatedTarget == popupObject.componentElement || domHelper.isAChildOf(popupObject.componentElement, event.relatedTarget)) {
					hidingRequired = false;
				} else if (event.relatedTarget == componentElement || domHelper.isAChildOf(componentElement, event.relatedTarget)) {
					hidingRequired = false;
				}
			}
			if (hidingRequired) {
				popupObject.attemptHideComponent();
			}
		}
		domHelper.removeClass(componentElement, 'menuitem_hover');
	};
	this.isVerticalPopup = function() {
		return verticalPopup;
	};
	init();
};