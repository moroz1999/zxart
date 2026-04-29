window.TabsBlockComponent = function(componentElement) {
    var self = this;
    var currentTabNumber = -1;
    var changeHandler;
    var tabId = false;
    var switchingEnabled = true;
    var tabsList = [];
    var contentList = [];

    var init = function() {
        var currentTabIndex = -1;
        tabId = 'tab_' + window.currentElementId;
        var tabElements = _('.tabs_list_item', componentElement);
        for (var i = 0; i < tabElements.length; i++) {
            var tab = new TabsBlockTabComponent(tabElements[i], i, self);
            if (tab.isActive()) {
                currentTabIndex = i;
            }
            tabsList.push(tab);
        }
        var elements = _('.tabs_content_item', componentElement);
        for (var i = 0; i < elements.length; i++) {
            var content = new TabsBlockContentComponent(elements[i], i);
            contentList.push(content);
        }
        if (contentList.length != tabsList.length) {
            throw 'Number of tab buttons and content blocks is not equal';
        }
        if (currentTabIndex < 0) {
            currentTabIndex = 0;
        }
        self.changeTab(currentTabIndex);
    };
    this.changeTab = function(newTabNumber) {
        if (switchingEnabled && currentTabNumber !== newTabNumber) {
            for (var i = 0; i < tabsList.length; i++) {
                tabsList[i].checkCurrent(newTabNumber);
            }
            for (var i = 0; i < contentList.length; i++) {
                contentList[i].checkCurrent(newTabNumber);
            }
            window.storageInterface.setValue(tabId, newTabNumber);
            currentTabNumber = newTabNumber;
            if (typeof changeHandler == 'function') {
                changeHandler();
            }
        }
    };
    this.setSwitchingEnabled = function(enabled) {
        switchingEnabled = enabled;
    };
    this.setChangeHandler = function(newChangeHandler) {
        changeHandler = newChangeHandler;
    };
    this.getCurrentTabNumber = function() {
        return currentTabNumber;
    };

    init();
};
window.TabsBlockTabComponent = function(componentElement, tabNumber, tabsContainer) {
    var CLASS_ACTIVE = 'tabs_list_active';
    var active = false;

    var init = function() {
        active = componentElement.className.indexOf(CLASS_ACTIVE) >= 0;
        eventsManager.addHandler(componentElement, 'click', clickHandler);
    };
    var clickHandler = function(event) {
        eventsManager.preventDefaultAction(event);
        tabsContainer.changeTab(tabNumber);
    };
    var refreshContents = function() {
        if (active) {
            domHelper.addClass(componentElement, CLASS_ACTIVE);
        } else {
            domHelper.removeClass(componentElement, CLASS_ACTIVE);
        }
    };
    this.checkCurrent = function(currentTabNumber) {
        active = tabNumber == currentTabNumber;
        refreshContents();
    };
    this.isActive = function() {
        return active;
    };
    init();
};
window.TabsBlockContentComponent = function(componentElement, tabNumber) {
    var active = false;

    var refreshContents = function() {
        componentElement.style.display = active ? '' : 'none';
    };
    this.checkCurrent = function(currentTabNumber) {
        active = tabNumber == currentTabNumber;
        refreshContents();
    };
};
