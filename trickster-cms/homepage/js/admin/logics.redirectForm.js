window.RedirectFormLogics = new function() {
    var initComponents = function() {
        var element = _('.redirect_form')[0];
        if (element) {
            new RedirectFormComponent(element);
        }
    };
    controller.addListener('initDom', initComponents);
};