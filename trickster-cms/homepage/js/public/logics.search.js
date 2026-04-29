window.searchLogics = new function() {
    var initComponents = function() {
        var elements = _('.search_form');
        for (var i = 0; i < elements.length; i++) {
            new SearchFormComponent(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};