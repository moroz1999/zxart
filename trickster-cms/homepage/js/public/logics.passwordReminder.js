window.passwordReminderLogics = new function() {
    var initComponents = function() {
        var elements = _('.passwordreminder_form');
        for (var i = 0; i < elements.length; i++) {
            new PasswordReminderFormComponent(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};