window.showFiltersLogics = new function() {
    var initComponents = function() {
        var element = _('.applicable_filters_form')[0];
        if (element) {
            new showFiltersComponent(element);
        }
    };
    controller.addListener('initDom', initComponents);
};