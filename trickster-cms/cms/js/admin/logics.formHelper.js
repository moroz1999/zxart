window.formHelperLogics = new function() {
    var initComponents = function() {
        var elements = document.querySelectorAll('.form_helper');
        for (var i = 0; i < elements.length; i++) {
            new FormHelperComponent(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};