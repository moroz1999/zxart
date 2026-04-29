window.ajaxFormLogics = new function() {
    var initComponents = function() {
        var elements = _('.ajax_form');
        for (var i = 0; i < elements.length; i++) {
            new AjaxFormComponent(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};