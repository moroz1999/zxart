window.textareaLogics = new function() {
    var initComponents = function() {
        var elements = _('.textarea_component');
        for (var i = 0; i < elements.length; i++) {
            new TextareaComponent(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};