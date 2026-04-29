window.checkBoxManager = new function() {
    var self = this;
    var checkBoxObjects = [];

    var init = function() {
        self.initCheckboxes(document);
    };
    this.initCheckboxes = function(element) {
        var inputElements = element.querySelectorAll('.checkbox_placeholder');
        for (var i = 0; i < inputElements.length; i++) {
            self.createCheckBox(inputElements[i]);
        }
    };

    this.createCheckBox = function(inputElement) {
        var found = false;
        for (var i = 0; i < checkBoxObjects.length; i++) {
            if (checkBoxObjects[i].inputElement == inputElement) {
                found = checkBoxObjects[i];
            }
        }
        if (!found) {
            var checkBoxObject = new CheckBoxComponent(inputElement);
            checkBoxObjects.push(checkBoxObject);
            found = checkBoxObject;
        }
        return found;
    };

    controller.addListener('initDom', init);
};