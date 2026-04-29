window.TabsBlockLogics = new function() {
    var initComponents = function() {
        var elements = _('.tabs_block_scripted');
        for (var i = 0; i < elements.length; i++) {
            new TabsBlockComponent(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};