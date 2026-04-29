window.PagerComponent = function(componentElement, contentList) {
    var self = this;
    var sticky = false;
    var update = function() {
        if (sticky) {
            componentElement.classList.add('sticky');
        } else {
            componentElement.classList.remove('sticky');
        }
    };
    this.setSticky = function(newSticky) {
        if (sticky !== newSticky) {
            sticky = newSticky;
            update();
        }
    };
    var init = function() {
    };
    init();
};
