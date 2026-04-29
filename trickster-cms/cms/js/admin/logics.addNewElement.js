window.newElementsLogics = new function() {
    var self = this;
    var componentsList;
    var infoIndex;
    var initLogics = function() {
        infoIndex = window.addNewElementInfoButtons;
    };
    var initComponents = function() {
        componentsList = [];
        var elements = _('.addnewelement_button');
        for (var i = 0; i < elements.length; i++) {
            componentsList.push(new AddNewElementComponent(elements[i]));
        }
    };
    this.getButtonInfo = function(buttonId) {
        var result = false;
        if (typeof infoIndex[buttonId] !== 'undefined') {
            result = infoIndex[buttonId];
        }
        return result;
    };
    controller.addListener('initLogics', initLogics);
    controller.addListener('initDom', initComponents);
};