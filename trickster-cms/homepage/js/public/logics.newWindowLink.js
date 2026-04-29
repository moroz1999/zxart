window.newWindowLinkLogics = new function() {
    var initComponents = function() {
        var elements = _('.newwindow_link');
        for (var i = 0; i < elements.length; i++) {
            new NewWindowLinkComponent(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};