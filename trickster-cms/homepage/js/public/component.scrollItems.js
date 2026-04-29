window.ScrollItemsComponent = function(componentElement) {
    var self = this;
    var pageCount = 0;
    var init = function() {
        var pageElements = _('.scrollitems_row', componentElement);
        pageCount = pageElements.length;
        if (pageCount > 1) {
            var containerElement = _('.scrollitems_container', componentElement);
            self.scrollPagesInit({
                'componentElement': containerElement,
                'resizeRequired': true,
                'autoStart': true,
                'pageElements': pageElements,
            });
            eventsManager.addHandler(componentElement, 'mouseenter', mouseEnter);
            eventsManager.addHandler(componentElement, 'mouseleave', mouseLeave);
            var elements = _('.scrollitems_previous', componentElement);
            if (elements.length) {
                eventsManager.addHandler(elements[0], 'click', previousClick);
            }
            elements = _('.scrollitems_next', componentElement);
            if (elements.length) {
                eventsManager.addHandler(elements[0], 'click', nextClick);
            }
        }
    };
    var previousClick = function(event) {
        self.stopPagesRotation();
        self.showPreviousPage();
        eventsManager.removeHandler(componentElement, 'mouseleave', mouseLeave);
    };
    var nextClick = function(event) {
        self.stopPagesRotation();
        self.showNextPage();
        eventsManager.removeHandler(componentElement, 'mouseleave', mouseLeave);
    };
    var mouseEnter = function(event) {
        self.stopPagesRotation();
    };
    var mouseLeave = function(event) {
        self.startPagesRotation();
    };
    init();
};
ScrollPagesMixin.call(ScrollItemsComponent.prototype);