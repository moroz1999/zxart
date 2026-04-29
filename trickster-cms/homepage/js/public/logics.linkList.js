window.linkListLogics = new function() {
    var initComponents = function() {
        var elements = _('.linklist');
        for (var i = 0; i < elements.length; i++) {
            new LinkListComponent(elements[i]);
        }
    };
    controller.addListener('DOMContentReady', initComponents);
};