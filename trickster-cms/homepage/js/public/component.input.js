window.InputComponent = function(parameters) {
    this.componentElement = false;
    this.inputElement = false;
    var self = this;
    var name;
    var value;
    var inputClass;
    var placeHolderSupport = false;
    var placeHolderText = '';
    var originalType = '';
    var init = function() {
        if (typeof parameters !== 'undefined') {
            parseParameters(parameters);
        }
        createDomStructure();

        eventsManager.addHandler(self.inputElement, 'focus', focusHandler);
        eventsManager.addHandler(self.inputElement, 'blur', blurHandler);

        placeHolderSupport = inputLogics.getPlaceHolderSupport();

        if (!placeHolderSupport) {
            if (placeHolderText = self.inputElement.getAttribute('placeholder')) {
                originalType = self.inputElement.getAttribute('type');
                restorePlaceHolder();
            }
        }
    };
    var createDomStructure = function() {
        if (!self.inputElement) {
            self.inputElement = document.createElement('input');
            self.inputElement.setAttribute('type', 'text');
            self.inputElement.setAttribute('autocomplete', 'off');
            var className = 'input_component';
            if (inputClass) {
                className += ' ' + inputClass;
            }
            self.inputElement.className = className;
            if (name) {
                self.inputElement.setAttribute('name', name);
            }
            if (value) {
                self.inputElement.setAttribute('value', value);
            }
        }
        self.componentElement = self.inputElement;
    };
    var parseParameters = function(parameters) {
        if (typeof parameters.name !== 'undefined') {
            name = parameters.name;
        }
        if (typeof parameters.componentElement !== 'undefined') {
            self.inputElement = parameters.componentElement;
        }
        if (typeof parameters.value !== 'undefined') {
            value = parameters.value;
        }
        if (typeof parameters.inputClass !== 'undefined') {
            inputClass = parameters.inputClass;
        }
    };
    this.getValue = function() {
        return self.inputElement.value;
    };
    this.setValue = function(value) {
        self.inputElement.value = value;
    };
    this.setDisabled = function(value) {
        self.inputElement.disabled = value;
    };
    this.setHandler = function(eventType, handler) {
        window.eventsManager.addHandler(self.inputElement, eventType, handler);
    };
    var focusHandler = function() {
        domHelper.removeClass(self.inputElement, 'input_error');
        if (!placeHolderSupport && originalType == 'text') {
            if (self.inputElement.value == placeHolderText) {
                self.inputElement.value = '';
                domHelper.addClass(self.inputElement, 'placeholder');
            }
        }
    };
    var blurHandler = function() {
        restorePlaceHolder();
    };
    var restorePlaceHolder = function() {
        if (self.inputElement.value == '' && originalType == 'text') {
            self.inputElement.value = placeHolderText;
            domHelper.removeClass(self.inputElement, 'placeholder');
        }
    };
    init();
};