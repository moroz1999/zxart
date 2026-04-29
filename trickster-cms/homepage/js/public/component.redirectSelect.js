window.RedirectSelectComponent = function(componentElement) {
    var init = function() {
        eventsManager.addHandler(componentElement, 'change', onChange);
    };
    var onChange = function(event) {
        document.location.href = getSelectedValue();
    };
    var getSelectedValue = function() {
        return componentElement.options[componentElement.selectedIndex].value;
    };
    init();
};