function AccordeonMenu(componentElement) {
    var init = function() {
        var elements = _('.accordeon_menu_item', componentElement);
        for (var i = 0; i < elements.length; i++) {
            new AccordeonMenuItem(elements[i]);
        }
    };

    var self = this;

    init();
}

function AccordeonMenuItem(componentElement) {
    var init = function() {
        if (componentElement.className.search('accordeon_menu_item_active') == -1) {
            if (titleElement = _('.accordeon_menu_item_title', componentElement)[0]) {
                if (contentElement = _('.accordeon_menu_item_submenu', componentElement)[0]) {
                    eventsManager.addHandler(componentElement, 'mouseenter', mouseOverHandler);
                    eventsManager.addHandler(componentElement, 'mouseleave', mouseOutHandler);
                }
            }
        }
    };
    var mouseOverHandler = function() {
        TweenLite.to(contentElement, 0.8, {'css': {'height': contentElement.scrollHeight}});
    };
    var mouseOutHandler = function() {
        TweenLite.to(contentElement, 0.5, {'css': {'height': 0}});
    };

    var titleElement = false;
    var contentElement = false;

    init();
}