window.translationFormLogics = new function() {
    var initComponents = function() {
        var element = _('.translation_form')[0];
        if (element) {
            new TranslationFormComponent(element);
        }
    };
    controller.addListener('initDom', initComponents);
};