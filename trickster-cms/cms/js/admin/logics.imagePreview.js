window.imagePreviewLogics = new function() {
    var containerElements;
    var initComponents = function() {
        containerElements = document.querySelectorAll('td.image_column');
        for (var i = 0; containerElements.length > i; i++) {
            new imagePreviewComponent(containerElements[i]);
        }
    };

    controller.addListener('initDom', initComponents);
};