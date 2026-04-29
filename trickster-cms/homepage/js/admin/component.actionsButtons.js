window.ActionsButtonsComponent = function(controlsElement, containerElement = false) {
    var hinted;
    var hint;
    var hintClassName = 'action_hint';
    var actionsButtons;
    var contentListElements;
    var contentListCheckedElements;
    var i;

    this.init = function() {
        actionsButtons = controlsElement.querySelectorAll('.actions_form_button');
        if (containerElement) {
            contentListElements = containerElement.querySelectorAll('.singlebox');
            for (i = contentListElements.length; i--;) {
                eventsManager.addHandler(contentListElements[i], 'change', isEnableActionsButtons);
            }
        }
        isEnableActionsButtons();
        initHints();
    };

    var isEnableActionsButtons = function() {
        var actionButtonsDisabled = true;
        if (containerElement) {
            contentListElements = containerElement.querySelectorAll('.singlebox');
            contentListCheckedElements = containerElement.querySelectorAll('.singlebox:checked');
            actionButtonsDisabled = !(contentListElements.length && contentListCheckedElements.length);
        }
        for (i = actionsButtons.length; i--;) {
            actionsButtons[i].disabled = actionButtonsDisabled;
        }
    };

    var initHints = function() {
        for (i = actionsButtons.length; i--;) {
            hinted = actionsButtons[i];
            hint = document.createElement('span');
            hint.classList.add(hintClassName);
            hint.innerHTML = translationsLogics.get('message.any_must_be_checked');
            hinted.appendChild(hint);
        }
    };

    this.init();
};