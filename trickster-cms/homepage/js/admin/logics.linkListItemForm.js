window.LinkListItemFormLogics = new function() {
    var initComponents = function() {
        var element = _('.linklistitem_form')[0];
        if (element) {
            new LinkListItemFormComponent(element);
        }
    };

    controller.addListener('initDom', initComponents);
};