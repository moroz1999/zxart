window.masterSelectComponent = function(componentElement, selectionType) {

    var options = [];
    var init = function() {
        window.eventsManager.addHandler(componentElement, 'change', changeHandler);
    };

    var changeHandler = function() {
        for (var i = 0; i < options.length; i++) {
            var servants = options[i].getServants();
            for (var j = 0; j < servants.length; j++) {
                if (selectionType == 'radio') {
                    if ((options[i].getOptionValue() == componentElement.value) && componentElement.checked) {
                        servants[j].hide();
                    } else {
                        servants[j].show();
                    }
                } else if (selectionType == 'checkbox') {
                    if ((options[i].getOptionValue() == componentElement.value)) {
                        if (componentElement.checked) {
                            servants[j].hide();
                        } else {
                            servants[j].show();
                        }
                    }
                } else {
                    if (options[i].getOptionValue() == componentElement.value) {
                        servants[j].hide();
                    } else {
                        servants[j].show();
                    }
                }
            }
        }
    };

    this.addOption = function(optionComponent) {
        options.push(optionComponent);
    };

    init();
};

window.masterOptionComponent = function(optionValue) {
    var servants = [];

    this.addServant = function(servantComponent) {
        servants.push(servantComponent);
    };

    this.getServants = function() {
        return servants;
    };

    this.getOptionValue = function() {
        return optionValue;
    };
};

window.servantFieldComponent = function(componentElement) {
    this.hide = function() {
        domHelper.addClass(componentElement, 'hidden_field');
    };

    this.show = function() {
        domHelper.removeClass(componentElement, 'hidden_field');
    };
};