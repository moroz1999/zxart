window.hiddenFieldsLogics = new function() {
    var servants = {};
    var initComponents = function() {

        if (window.hiddenFieldsData) {
            for (var t = 0; t < window.hiddenFieldsData.length; t++) {
                for (var i = 0; i < window.hiddenFieldsData[t].length; i++) {
                    var currentFieldData = window.hiddenFieldsData[t][i];
                    var elementWrapper = _('.field' + currentFieldData.selectId)[0];
                    if (currentFieldData.selectionType == 'radiobutton') {
                        var masterElements = _('.radio_holder', elementWrapper);
                        var selectionType = 'radio';
                    } else if (currentFieldData.selectionType == 'checkbox') {
                        var masterElements = _('.checkbox_placeholder', elementWrapper);
                        var selectionType = 'checkbox';
                    } else {
                        var masterElements = _('.dropdown_placeholder', elementWrapper);
                        var selectionType = 'dropdown';
                    }

                    for (var l = 0; l < masterElements.length; l++) {
                        var selectComponent = new masterSelectComponent(masterElements[l], selectionType);

                        for (var k = 0; k < currentFieldData.options.length; k++) {
                            var optionComponent = new masterOptionComponent(currentFieldData.options[k].optionValue);
                            selectComponent.addOption(optionComponent);

                            for (var j = 0; j < currentFieldData.options[k].fields.length; j++) {
                                if (typeof servants[currentFieldData.options[k].fields[j]] == 'undefined') {
                                    var servantElement = _('.field' + currentFieldData.options[k].fields[j])[0];
                                    var servantComponent = new servantFieldComponent(servantElement);
                                    servants[currentFieldData.options[k].fields[j]] = servantComponent;
                                }
                                optionComponent.addServant(servants[currentFieldData.options[k].fields[j]]);
                            }
                        }
                        window.eventsManager.fireEvent(masterElements[l], 'change');
                    }
                }
            }
        }
    };

    controller.addListener('initDom', initComponents);
};