//deprecated
// use SubMenuItemComponent
window.MainMenuComponent = function(componentElement) {
    var init = function() {
        self.id = parseInt(componentElement.className.split('menuid_')[1], 10);
        if (menuInfo = window.mainMenuLogics.getMenuInfo(self.id)) {
            var subMenuList = window.mainMenuLogics.getSubMenuInfo(self.id);
            if (subMenuList.length > 0) {
                popupObject = new SubMenuPopupComponent(subMenuList, self, componentElement);
            }
            window.eventsManager.addHandler(componentElement, 'mouseenter', self.mouseEnterHandler);
            window.eventsManager.addHandler(componentElement, 'mouseleave', self.mouseLeaveHandler);
        }
    };
    this.mouseEnterHandler = function() {
        domHelper.addClass(componentElement, 'menuitem_hover');
        controller.fireEvent('hideVisibleSubmenuPopups');
        if (popupObject) {
            popupObject.displayComponent();
        }
    };
    this.mouseLeaveHandler = function(event) {
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

    var self = this;

    var menuInfo = false;
    var popupObject = false;
    this.id = false;

    init();
};
window.SubMenuPopupComponent = function(subMenusList, menuComponent, referenceElement) {
    var self = this;
    var componentElement;
    var backgroundElement;
    var contentElement;
    var arrowElement;
    var displayed = false;
    var hideTimeout;

    this.componentElement = false;
    var init = function() {
        createDomStructure();
        window.eventsManager.addHandler(componentElement, 'mouseenter', menuComponent.mouseEnterHandler);
        window.eventsManager.addHandler(componentElement, 'mouseleave', menuComponent.mouseLeaveHandler);
        controller.addListener('hideVisibleSubmenuPopups', hideComponent);
    };
    var createDomStructure = function() {
        componentElement = document.createElement('div');

        componentElement.className = 'submenus_popup_block';
        componentElement.style.opacity = 0;

        document.body.appendChild(componentElement);

        backgroundElement = document.createElement('div');
        backgroundElement.className = 'submenus_popup_background';
        componentElement.appendChild(backgroundElement);

        arrowElement = document.createElement('div');
        arrowElement.className = 'submenus_popup_arrow';
        backgroundElement.appendChild(arrowElement);

        contentElement = document.createElement('div');
        contentElement.className = 'submenus_popup_content';
        componentElement.appendChild(contentElement);

        for (var i = 0; i < subMenusList.length; i++) {
            var subMenu = new SubMenusPopupItemComponent(subMenusList[i]);
            contentElement.appendChild(subMenu.componentElement);
        }
        self.componentElement = componentElement;
    };
    this.displayComponent = function() {
        window.clearTimeout(hideTimeout);
        if (!displayed) {
            displayed = true;
            var menuWidth = referenceElement.offsetWidth;
            var menuHeight = referenceElement.offsetHeight;

            componentElement.style.display = 'block';
            if (componentElement.offsetWidth < menuWidth) {
                componentElement.style.minWidth = menuWidth + 'px';
            }

            var positions = window.domHelper.getElementPositions(referenceElement);
            componentElement.style.left = positions.x + 'px';
            componentElement.style.top = (positions.y + menuHeight) + 'px';

            TweenLite.to(componentElement, 0.5, {'css': {'opacity': 1}});
        }
    };
    this.attemptHideComponent = function() {
        hideTimeout = window.setTimeout(startHideComponent, 300);
    };
    var startHideComponent = function() {
        displayed = false;
        TweenLite.to(componentElement, 0.3, {'css': {'opacity': 0}, 'onComplete': hideComponent});
    };
    var hideComponent = function() {
        componentElement.style.display = 'none';
        displayed = false;
    };

    init();
};
window.SubMenusPopupItemComponent = function(menuInfo) {
    var init = function() {
        self.componentElement = document.createElement('a');
        self.componentElement.href = menuInfo.URL;
        self.componentElement.className = 'submenus_popup_item';

        var subElement1 = document.createElement('div');
        subElement1.className = 'submenus_popup_item_left';
        self.componentElement.appendChild(subElement1);
        var subElement2 = document.createElement('div');
        subElement2.className = 'submenus_popup_item_center';
        self.componentElement.appendChild(subElement2);
        var contentElement = document.createElement('div');
        contentElement.className = 'submenus_popup_item_content';
        self.componentElement.appendChild(contentElement);

        contentElement.innerHTML = menuInfo.title;
    };
    var self = this;
    this.componentElement = false;

    init();
};