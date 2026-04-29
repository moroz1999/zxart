window.fancyTitleLogics = new function() {
    var init = function() {
        var elements = _('.fancytitle');
        for (var i = elements.length; i--;) {
            new FancyTitleComponent(elements[i]);
        }
    };
    controller.addListener('initDom', init);
};