window.radioButtonManager = new function() {
    var init = function() {
        var inputElements = _('.radio_holder');
        for (var i = 0; i < inputElements.length; i++) {
            if (!inputElements[i].artWebRadioCreated) {
                var radioObject = new RadioButtonComponent(inputElements[i]);
                if (!radioObjects[radioObject.name]) {
                    radioObjects[radioObject.name] = [];
                }
                radioObjects[radioObject.name].push(radioObject);
            }
        }
    };
    this.makeRadioButtons = function(parentElement) {
        var inputElements = _('.radio_holder', parentElement);
        for (var i = 0; i < inputElements.length; i++) {
            if (!inputElements[i].artWebRadioCreated) {
                var radioObject = new RadioButtonComponent(inputElements[i]);
                if (!radioObjects[radioObject.name]) {
                    radioObjects[radioObject.name] = [];
                }
                radioObjects[radioObject.name].push(radioObject);
            }
        }
    };
    this.refresh = function(name, value) {
        if (radioObjects[name]) {
            for (var i = 0; i < radioObjects[name].length; i++) {
                radioObjects[name][i].refresh(value);
            }
        }
    };
    var radioObjects = {};

    controller.addListener('initDom', init);
};