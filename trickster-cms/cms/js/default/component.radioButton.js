function RadioButtonComponent(inputElement) {
    var radioButton = null;

    this.checked = false;
    this.value = false;
    this.name = false;

    var self = this;
    var importRadioButtonData = function() {
        if ((inputElement.tagName === 'input' || inputElement.tagName === 'INPUT') && inputElement.type === 'radio') {
            inputElement.artWebRadioCreated = true;
            self.name = inputElement.name;
            self.value = inputElement.value;
            inputElement.addEventListener('change', changeHandler);
        }
    };
    var createRadioButton = function() {
        radioButton = document.createElement('span');
        radioButton.className = 'radiobutton';
        radioButton.tabIndex = 0;

        radioButton.addEventListener('click', click);
        radioButton.addEventListener('keydown', keyHandler);

        var parent = inputElement.parentNode;
        parent.insertBefore(radioButton, inputElement);
    };
    var hideInputElement = function() {
        inputElement.style.display = 'none';
    };
    var click = function() {
        inputElement.checked = true;
        window.eventsManager.fireEvent(inputElement, 'change');
    };
    var changeHandler = function() {
        window.radioButtonManager.refresh(self.name);
    };
    var keyHandler = function(event) {
        if (event.code === 'Space') {
            inputElement.checked = true;
            window.eventsManager.fireEvent(inputElement, 'change');
        }
    };

    this.refresh = function() {
        self.checked = inputElement.checked;
        if (self.checked) {
            radioButton.className = 'radiobutton radiobutton_checked';
        } else {
            radioButton.className = 'radiobutton';
        }
    };

    importRadioButtonData();
    createRadioButton();
    hideInputElement();
    self.refresh();
}