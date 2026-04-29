window._ = function(selector, element) {
    if (!element) {
        element = document;
    }
    return element.querySelectorAll(selector);
};