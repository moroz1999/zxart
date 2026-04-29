window.fileInputLogics = new function() {
    var initComponents = function() {
        var elements = _('.fileinput_placeholder');
        for (var i = 0; i < elements.length; i++) {
            new FileInputComponent(elements[i]);
        }
    };
    controller.addListener('initDom', initComponents);
};