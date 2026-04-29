function CheckBoxComponent(inputElement) {
    var componentElement = null;
    var checked = false;
    var self = this;
    var classNames;

    var init = function() {
        importCheckBoxData();
        createCheckBox();
        hideInputElement();
    };
    var importCheckBoxData = function() {
        if ((inputElement.tagName === 'input' || inputElement.tagName === 'INPUT') && inputElement.type === 'checkbox') {
            checked = inputElement.checked;
        }
        inputElement.addEventListener('change', self.synchronize);
    };
    var createCheckBox = function() {
        componentElement = document.createElement('span');
        componentElement.tabIndex = 0;
        componentElement.className = 'checkbox';
        classNames = '';
        var inputClassNames = inputElement.className.split(' ');
        for (var i = 0; i < inputClassNames.length; i++) {
            if (inputClassNames[i] !== 'checkbox_placeholder') {
                classNames += ' ' + inputClassNames[i];
            }
        }
        if (checked) {
            componentElement.className = 'checkbox checked';
        }
        componentElement.className += classNames;
        componentElement.addEventListener('click', click);
        componentElement.addEventListener('keydown', keyHandler);

        var parent = inputElement.parentNode;
        parent.insertBefore(componentElement, inputElement);
    };
    var hideInputElement = function() {
        inputElement.style.display = 'none';
    };
    var click = function(event) {
        event.stopPropagation();
        inputElement.checked = !inputElement.checked;
        window.eventsManager.fireEvent(inputElement, 'change');
    };
    var keyHandler = function(event) {
        event.stopPropagation();
        if (event.code === 'Space') {
            inputElement.checked = !inputElement.checked;
            window.eventsManager.fireEvent(inputElement, 'change');
        }
    };
    this.synchronize = function() {
        checked = inputElement.checked;
        if (checked) {
            componentElement.className = 'checkbox checked' + classNames;
        } else {
            componentElement.className = 'checkbox' + classNames;
        }
    };

    init();
}