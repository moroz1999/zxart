window.loginLogics = new function() {
    var initComponents = function() {
        var elements = _('.login_component');
        for (var i = 0; i < elements.length; i++) {
            new LoginComponent(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};