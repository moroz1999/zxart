window.visitorLogics = new function() {
    var initComponents = function() {
        var elements = _('.spoiler_component');
        for (var i = 0; i < elements.length; i++) {
            new SpoilerComponent(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};