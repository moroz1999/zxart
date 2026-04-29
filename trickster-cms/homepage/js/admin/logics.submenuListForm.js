window.submenuListFormLogics = new function() {
    var initComponents = function() {
        var elements = _('.submenulist_form_block');
        for (var i = 0; i < elements.length; i++) {
            new SubmenuListFormComponent(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};