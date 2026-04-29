window.LinkListFormLogics = new function() {
    var initComponents = function() {
        var element = _('.linklist_form')[0];
        if (element) {
            new LinkListFormComponent(element);
        }
    };
    controller.addListener('initDom', initComponents);
};