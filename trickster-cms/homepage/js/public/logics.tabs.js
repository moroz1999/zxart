window.tabsLogics = new function() {
    var initComponents = function() {
        var elements = _('.tabs');
        for (var i = elements.length; i--;) {
            new TabsComponent(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};