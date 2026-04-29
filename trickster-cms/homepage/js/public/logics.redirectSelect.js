window.redirectSelectLogics = new function() {
    var initComponents = function() {
        var elements = _('select.redirect_select');
        for (var i = 0; i < elements.length; i++) {
            new RedirectSelectComponent(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};