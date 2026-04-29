window.ajaxSelectLogics = new function() {
    var initComponents = function() {
        //todo: move automatic logics to appropriate form components javascript
        var elements = _('.ajaxselect');
        for (var i = 0; i < elements.length; i++) {
            new AjaxSelectComponent(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};