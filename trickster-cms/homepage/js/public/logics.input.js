window.inputLogics = new function() {
    var placeHolderSupport = null;
    var initComponents = function() {
        var elements = _('.input_component');
        for (var i = 0; i < elements.length; i++) {
            new InputComponent({'componentElement': elements[i]});
        }
    };
    this.getPlaceHolderSupport = function() {
        if (placeHolderSupport === null) {
            placeHolderSupport = ('placeholder' in document.createElement('input'));
        }
        return placeHolderSupport;
    };
    controller.addListener('initDom', initComponents);
};