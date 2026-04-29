window.dropDownManager = new function() {
    this.getDropDown = function(element, parameters) {
        if (typeof parameters == 'undefined') {
            parameters = {};
        }
        var dropDown = checkDropDown(element);
        if (!dropDown) {
            dropDown = manufactureDropDown(element, parameters);
        }
        return dropDown;
    };
    this.updateDropDown = function(element) {
        var dropDown = checkDropDown(element);
        if (dropDown) {
            dropDown.update();
        }
    };
    this.hideLists = function() {
        for (var i = 0; i < dropDownObjectsList.length; i++) {
            dropDownObjectsList[i].hideList();
        }
    };
    this.createDropDown = function(parameters) {
        var dropDown = new DropDownComponent(false, parameters);
        dropDownObjectsList.push(dropDown);
        return dropDown;
    };
    var manufactureDropDown = function(element, parameters) {
        var dropDown = new DropDownComponent(element, parameters);
        dropDownObjectsList.push(dropDown);
        return dropDown;
    };
    var init = function() {
        window.eventsManager.addHandler(window, 'click', clickHandler);

        self.initDropdowns(document);
    };
    this.initDropdowns = function(element) {
        var dropDownElements = element.querySelectorAll('.dropdown_placeholder');
        for (var i = 0; i < dropDownElements.length; i++) {
            if (!checkDropDown(dropDownElements[i])) {
                var referenceElement = dropDownElements[i].nextSibling || null;
                var parent = dropDownElements[i].parentNode;
                var dropDownObject = manufactureDropDown(dropDownElements[i]);
                parent.insertBefore(dropDownObject.componentElement, referenceElement);
            }
        }
    };
    var checkDropDown = function(element) {
        var result = false;
        for (var i = 0; i < dropDownObjectsList.length; i++) {
            if (dropDownObjectsList[i].selectorElement == element) {
                result = dropDownObjectsList[i];
                break;
            }
        }
        return result;
    };
    var clickHandler = function() {
        for (var i = 0; i < dropDownObjectsList.length; i++) {
            dropDownObjectsList[i].hideList();
        }
    };

    var self = this;
    var dropDownObjectsList = [];

    controller.addListener('initDom', init);
};