window.lazyImageLogics = new function() {
    var initComponents = function() {
        var elements = document.querySelectorAll('.lazy_image');
        for (var i = elements.length; i--;) {
            new LazyImageComponent(elements[i]);
        }
    };
    controller.addListener('startApplication', initComponents);
    controller.addListener('initLazyImages', initComponents);
};