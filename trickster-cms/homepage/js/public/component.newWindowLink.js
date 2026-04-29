window.NewWindowLinkComponent = function(linkElement) {
    var init = function() {
        eventsManager.addHandler(linkElement, 'click', clickHandler);
    };
    var clickHandler = function(event) {
        eventsManager.preventDefaultAction(event);
        window.open(linkElement.href);
    };
    var self = this;
    init();
};