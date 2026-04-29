window.TabsComponent = function(componentElement) {
    var self = this;
    var activeTabNumber = 0;
    var rememberOpenedTab;

    var contentElements = [];
    var buttons = [];

    var init = function() {
        if (componentElement.className.indexOf('tabs_remember_opened') !== -1) {
            rememberOpenedTab = true;
        }

        var elements;
        if (elements = _('.tab_button', componentElement)) {
            for (var i = 0; i != elements.length; i++) {
                buttons[buttons.length] = new TabsButtonComponent(elements[i], self, i);
                if (elements[i].className.indexOf('tab_button_active') != -1) {
                    activeTabNumber = i;
                }
            }
            var newTabNumber = activeTabNumber;
            if (rememberOpenedTab) {
                var storedTabNumber = storageInterface.getValue('tabs ' + componentElement.className);
                if (storedTabNumber !== false) {
                    newTabNumber = storedTabNumber;
                }
            }
            contentElements = _('.tabs_item', componentElement);
            self.activateTab(newTabNumber);
        }
    };
    this.activateTab = function(newTabNumber, userInteracted) {
        if (rememberOpenedTab && userInteracted) {
            storageInterface.setValue('tabs ' + componentElement.className, newTabNumber);
        }
        if (buttons[activeTabNumber]) {
            buttons[activeTabNumber].deActivate();
        }
        if (buttons[newTabNumber]) {
            buttons[newTabNumber].activate();
        }
        domHelper.removeClass(contentElements[activeTabNumber], 'tabs_item_active');
        domHelper.addClass(contentElements[newTabNumber], 'tabs_item_active');
        activeTabNumber = newTabNumber;
        controller.fireEvent('TabsComponent.tabActivated');
    };
    this.getActiveTabNumber = function() {
        return activeTabNumber;
    };

    init();
};

window.TabsButtonComponent = function(componentElement, tabsComponent, number) {
    var init = function() {
        eventsManager.addHandler(componentElement, 'click', onClick);
    };
    var onClick = function() {
        if (number != tabsComponent.getActiveTabNumber()) {
            tabsComponent.activateTab(number, true);
        }
    };
    this.activate = function() {
        domHelper.addClass(componentElement, 'tab_button_active');
    };
    this.deActivate = function() {
        domHelper.removeClass(componentElement, 'tab_button_active');
    };
    init();
};