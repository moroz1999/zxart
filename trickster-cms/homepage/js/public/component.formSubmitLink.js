window.FormSubmitLinkComponent = function(componentElement) {
    var self = this;
    var formElement;

    var init = function() {

        formElement = findParentForm(componentElement);
        if (formElement) {
            eventsManager.addHandler(componentElement, 'click', submitForm);
        }
    };

    var findParentForm = function(element) {
        var parent = element.parentNode;
        if (parent && parent.tagName.toUpperCase() != 'FORM') {
            parent = findParentForm(parent);
        }
        return parent;
    };

    var submitForm = function(event) {
        if (event) {
            eventsManager.preventDefaultAction(event);
        }
        formElement.submit();
    };

    init();
};