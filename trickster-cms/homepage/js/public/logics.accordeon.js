window.accordeonLogics = new function() {
    var initComponents = function() {
        var elements = _('.accordeon');
        for (var i = 0; i < elements.length; i++) {
            new Accordeon(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};