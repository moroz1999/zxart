window.GenericFormComponent = function(componentElement, index) {
    var self = this;
    this.componentElement = componentElement;

    var init = function() {
        // 1st form element on the page
        if (index === 0) {
            selectFirstInputElement();
        }
        var elements = componentElement.querySelectorAll('.form_controls .button');
        for (var i = 0; i < elements.length; i++) {
            new FormControlsButtonComponent(elements[i]);
        }
    };

    var selectFirstInputElement = function() {
        var formChildElements = _('*', componentElement);

        for (var i = 0, l = formChildElements.length; i !== l; i++) {
            var formChildElement = formChildElements[i];

            var tagName = formChildElement.tagName.toLowerCase();
            if (tagName !== 'input' && formChildElement.type !== 'text') {
                if (tagName === 'select') {
                    return;
                }
            } else {
                formChildElement.focus();
                var v = formChildElement.value;
                formChildElement.value = '';
                formChildElement.value = v;
                return;
            }
        }
    };

    init();
};