window.formSubmitLinkLogics = new function() {
    var init = function() {
        var elements = _('.formsubmitlink_component');
        for (var i = 0, l = elements.length; i !== l; i++) {
            new FormSubmitLinkComponent(elements[i]);
        }
    };
    controller.addListener('initDom', init);
};