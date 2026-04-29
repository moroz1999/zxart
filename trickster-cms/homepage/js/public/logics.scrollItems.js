window.scrollItemsLogics = new function() {
    var initComponents = function() {
        var elements = _('.scrollitems');
        for (var i = elements.length; i--;) {
            new ScrollItemsComponent(elements[i]);
        }
    };
    controller.addListener('DOMContentReady', initComponents);
};