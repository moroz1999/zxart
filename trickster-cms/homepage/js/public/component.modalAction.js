window.ModalActionComponent = function(checkboxElement, footerElement, elementForPosition, additionalClassName, bubbleCloseTag, message) {
    var self = this;
    var html = document.documentElement;
    var htmlScrollTopStart;
    var selfModalComponent = new ModalComponent(elementForPosition, bubbleCloseTag);

    var init = function() {
        var makeElement = selfModalComponent.makeElement;
        selfModalComponent.addClass('modal_buttons ' + additionalClassName);
        if (message && message['title']) {
            selfModalComponent.setTitle(message['title']);
        }
        if (message && message['content']) {
            selfModalComponent.setContent(message['content']);
        }

        var fragment = document.createDocumentFragment();
        var submitButtonElement, footerElements;
        if (checkboxElement && message && message['footer']) {
            submitButtonElement = makeElement('div', 'submit button', fragment);
            submitButtonElement.innerHTML = message['footer'];
            // htmlScrollTopStart = html.scrollTop;
            eventsManager.addHandler(submitButtonElement, 'click', submitClick);
        } else if (footerElement === 'multiple' && message && message['footer']) {
            footerElements = makeElement('div', 'modal_footer_inner buttons', fragment);
            footerElements.innerHTML = message['footer'];
        }

        selfModalComponent.setControls(fragment);
        selfModalComponent.setDisplayed(true);

        if (bubbleCloseTag) {
            document.querySelector('.' + additionalClassName + ' .' + bubbleCloseTag).addEventListener('click', function(ev) {
                ev.preventDefault();
                selfModalComponent.setDisplayed(false);
            });

        }
    };
    var submitClick = function(event) {
        eventsManager.preventDefaultAction(event);
        checkboxElement.checked = !checkboxElement.checked;
        window.eventsManager.fireEvent(checkboxElement, 'change');
        var formElement = checkboxElement.form;
        selfModalComponent.setDisplayed(false);

        formElement.submit();
    };

    init();
};