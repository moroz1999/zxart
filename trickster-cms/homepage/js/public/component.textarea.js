function TextareaComponent(textareaBlockElement) {
    var init = function() {
        eventsManager.addHandler(textareaBlockElement, 'click', clickHandler);

        originalClass = textareaBlockElement.className;
        if (textareaBlockElement.tagName.toLowerCase() == 'textarea') {
            textareaElement = textareaBlockElement;
        } else if (textareaElement = textareaBlockElement.querySelector('textarea')) {
            eventsManager.addHandler(textareaElement, 'focus', focusHandler);
            eventsManager.addHandler(textareaElement, 'blur', blurHandler);
        }

        eventsManager.addHandler(textareaBlockElement, 'mouseenter', mouseOverHandler);
        eventsManager.addHandler(textareaBlockElement, 'mouseleave', mouseOutHandler);

        defaultTextElement = textareaBlockElement.querySelector('.textarea_component_default');

        refresh();
    };
    var clickHandler = function() {
        textareaElement.focus();
    };
    var mouseOverHandler = function() {
        hovered = true;
        refresh();
    };
    var mouseOutHandler = function() {
        hovered = false;
        refresh();
    };
    var focusHandler = function() {
        domHelper.addClass(textareaBlockElement, 'textarea_component_focused');
        focused = true;
        refresh();
    };
    var blurHandler = function() {
        domHelper.removeClass(textareaBlockElement, 'textarea_component_focused');
        focused = false;
        refresh();
    };
    var refresh = function() {
        var newClass = originalClass;

        if (hovered) {
            newClass = newClass + ' textarea_component_hovered';
        }
        if (focused) {
            newClass = newClass + ' textarea_component_focused';
            if (defaultTextElement) {
                defaultTextElement.style.display = 'none';
            }
        } else {
            if (defaultTextElement) {
                if (textareaElement.value == '') {
                    defaultTextElement.style.display = 'block';
                } else {
                    defaultTextElement.style.display = 'none';
                }
            }
        }
        textareaBlockElement.className = newClass;
    };

    var focused = false;
    var hovered = false;

    var originalClass = false;
    var defaultTextElement = false;
    var textareaElement = false;

    init();
}
