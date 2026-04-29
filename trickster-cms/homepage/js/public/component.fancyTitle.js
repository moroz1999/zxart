window.FancyTitleComponent = function(componentElement) {
    var init = function() {
        if (componentElement.title) {
            new ToolTipComponent(componentElement, componentElement.title);
            componentElement.title = '';
        }
    };
    init();
};