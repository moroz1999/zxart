window.tableComponentLogics = new function() {
    var init = function() {
        var elements = _('.table_component');
        for (var i = elements.length; i--;) {
            new TableComponent(elements[i]);
        }
    };
    controller.addListener('initDom', init);
};