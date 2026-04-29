window.languagesFormLogics = new function() {
    var initComponents = function() {
        var element = _('.languages_form')[0];
        if (element) {
            new LanguagesFormComponent(element);
        }
    };
    controller.addListener('initDom', initComponents);
};