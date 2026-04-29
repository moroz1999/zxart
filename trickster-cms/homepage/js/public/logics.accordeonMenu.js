window.accordeonMenuLogics = new function() {
    var initComponents = function() {
        var elements = _('.accordeon_menu');
        for (var i = 0; i < elements.length; i++) {
            new AccordeonMenu(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};