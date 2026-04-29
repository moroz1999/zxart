window.GenericFormLogics = new function() {
    var initComponents = function() {
        var elements = _('.form_component');
        for (var i = 0; i < elements.length; i++) {
            new GenericFormComponent(elements[i], i);
        }
    };
    controller.addListener('initDom', initComponents);
};