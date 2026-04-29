window.radioTabsLogics = new function() {

    var initComponents = function() {
        var elements = _('.radio_tabs_component');
        for (var i = 0; i < elements.length; i++) {
            new RadioTabsComponent(elements[i]);
        }
    };

    controller.addListener('initDom', initComponents);
};