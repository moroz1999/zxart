window.ContentListComponent = function(componentElement) {
    var self = this;
    var formElement;
    var actionInput;
    var idInput;
    var elementIdInput;
    var tableElement;
    var bottomElement;
    var pagerComponent;
    var init = function() {
        var i;
        if (formElement = _('.content_list_form', componentElement)[0]) {
            if (actionInput = _('.content_list_form_action', formElement)[0]) {
                var buttons;
                buttons = _('.actions_form_button', formElement);
                if (buttons.length > 0) {
                    for (i = 0; i < buttons.length; i++) {
                        new ActionButtonComponent(buttons[i], self);
                    }
                }
                idInput = _('.content_list_form_id', formElement)[0];
                elementIdInput = _('.content_list_form_elementid', formElement)[0];
            }
        }
        var elements = _('.content_list_item', componentElement);
        for (i = elements.length; i--;) {
            new ContentListItemComponent(elements[i]);
        }
        bottomElement = componentElement.querySelector('.content_list_bottom');
        tableElement = componentElement.querySelector('.content_list');
        if (tableElement) {
            var element = componentElement.querySelector('.pager_block');
            if (element) {
                pagerComponent = new PagerComponent(element, tableElement);

                window.addEventListener('scroll', updatePager);
                window.addEventListener('load', updatePager);
            }
        }

    };
    this.buttonClicked = function(action, targetId, formUrl) {
        actionInput.value = action;
        if (targetId) {
            idInput.value = targetId;
        }
        if (formUrl) {
            formElement.setAttribute('action', formUrl);
            formElement.action = formUrl;
        }
        formElement.submit();
    };

    var updatePager = function() {
        if (self.isOnScreen(tableElement) && !self.isOnScreen(bottomElement)) {
            pagerComponent.setSticky(true);
        } else {
            pagerComponent.setSticky(false);
        }
    };

    init();
};

DomHelperMixin.call(ContentListComponent.prototype);

window.ContentListItemComponent = function(componentElement) {
    var deleteButton;
    var name;

    var init = function() {
        var element;

        if (element = componentElement.querySelector('.content_item_title')) {
            name = element.innerHTML;
        }

        if (deleteButton = componentElement.querySelector('.content_item_delete_button')) {
            eventsManager.addHandler(deleteButton, 'click', deleteButtonClick);
        }
    };

    var deleteButtonClick = function(event) {
        eventsManager.preventDefaultAction(event);
        var msg;
        if (name) {
            msg = window.translationsLogics.get(['message.deleteelementconfirmation']).replace('%s', name);
        } else {
            msg = window.translationsLogics.get(['message.deleteunknownelementconfirmation']);
        }
        // decode html entities
        var temp = document.createElement('div');
        temp.innerHTML = msg;
        msg = temp.childNodes[0].nodeValue;
        temp.removeChild(temp.firstChild);

        if (confirm(msg)) {
            window.location.href = deleteButton.href;
        }
    };
    init();
};

window.ActionButtonComponent = function(componentElement, parentObject) {
    var action;
    var confirmation;
    var targetId;
    var actionUrl;
    var init = function() {
        action = componentElement.getAttribute('data-action');
        targetId = componentElement.getAttribute('data-targetid');
        confirmation = componentElement.getAttribute('data-confirmation');
        actionUrl = componentElement.getAttribute('data-url');
        eventsManager.addHandler(componentElement, 'click', clickHandler);
    };
    var clickHandler = function(event) {
        eventsManager.preventDefaultAction(event);
        if (confirmation) {
            if (confirm(confirmation)) {
                parentObject.buttonClicked(action, targetId, actionUrl);
            }
        } else {
            parentObject.buttonClicked(action, targetId, actionUrl);
        }
    };
    init();
};