window.searchLogics = new function() {
    var initComponents = function() {
        var elements = _('.content_filter_form');
        for (var i = 0; i < elements.length; i++) {
            new ContentFilterForm(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};