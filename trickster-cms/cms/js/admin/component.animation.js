window.animationComponent = function(button, element, animation, callBack) {
    var init = function() {
        clickHandler();
    };

    var clickHandler = function() {
        if (animation) {
            var showClass = animation + '_in';
        }
        if (!callBack) {
            eventsManager.addHandler(button, 'click', function() {
                if (element.classList.contains(showClass)) {
                    element.classList.remove(showClass);
                } else if (!element.classList.contains(showClass)) {
                    element.classList.add(showClass);
                }
            });
        } else {
            callBack();
        }
    };

    init();
};