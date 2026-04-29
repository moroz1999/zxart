function LoginComponent(componentElement) {
    var self = this;
    var formComponent;
    var shortComponent;

    this.componentElement = null;

    var init = function() {
        self.componentElement = componentElement;
        var element = _('.login_form_block', componentElement)[0];
        if (element) {
            formComponent = new LoginFormComponent(element, self);
            if (_('.login_error', componentElement).length > 0) {
                self.showForm();
            }
        }
        element = _('.login_short', componentElement)[0];
        if (element) {
            shortComponent = new LoginShortComponent(element, self);
        }
    };
    this.showForm = function() {
        if (formComponent.popupEnabled) {
            formComponent.displayComponent();
        }
    };

    init();
}

function LoginShortComponent(componentElement, parentObject) {
    var displayButton = null;
    var init = function() {
        if (displayButton = _('.login_short_button', componentElement)[0]) {
            eventsManager.addHandler(displayButton, 'click', displayButtonClickHandler);
        }
    };
    var displayButtonClickHandler = function(event) {
        eventsManager.preventDefaultAction(event);
        parentObject.showForm();
    };
    init();
}

function LoginFormComponent(componentElement, parentObject) {
    var self = this;
    var submitButton = null;
    var formElement = null;
    var displayed = false;

    this.popupEnabled = false;
    var init = function() {
        if (formElement = _('.login_form', componentElement)[0]) {
            if (submitButton = _('.login_popup_button', formElement)[0]) {
                eventsManager.addHandler(submitButton, 'click', submitForm);
            }
            eventsManager.addHandler(componentElement, 'keydown', checkKey);
            if (componentElement.className.search('login_form_popup') != -1) {
                self.popupEnabled = true;
                eventsManager.addHandler(componentElement, 'click', clickHandler);
            }

        }
    };
    var checkKey = function(event) {
        if (event.keyCode == 13) {
            submitForm();
        }
    };
    var submitForm = function(event) {
        if (event) {
            eventsManager.preventDefaultAction(event);
        }
        formElement.submit();
    };
    this.displayComponent = function() {
        if (!displayed) {
            displayed = true;
            domHelper.addClass(componentElement, 'login_form_visible');
            window.setTimeout(function() {
                // let the event that triggered display bubble to body before adding listener
                eventsManager.addHandler(document.body, 'click', self.hideComponent);
            }, 0);
        }
    };
    this.hideComponent = function() {
        if (displayed) {
            displayed = false;
            domHelper.removeClass(componentElement, 'login_form_visible');
            eventsManager.removeHandler(document.body, 'click', self.hideComponent);
        }
    };
    var adjustPositions = function() {
        var positions = domHelper.getElementPositions(parentObject.componentElement);

        var left = positions.x + parentObject.componentElement.offsetWidth - componentElement.offsetWidth - 25;
        var top = positions.y + parentObject.componentElement.offsetHeight + 10;

        componentElement.style.top = top + 'px';
        componentElement.style.left = left + 'px';
    };
    var clickHandler = function(event) {
        eventsManager.cancelBubbling(event);
    };

    init();
}