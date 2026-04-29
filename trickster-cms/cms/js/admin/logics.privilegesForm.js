window.privilegesFormLogics = new function() {
    var initComponents = function() {
        var elements = document.querySelectorAll('.privilegesform_component');
        for (var i = 0; i < elements.length; i++) {
            new PrivilegesFormComponent(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};