window.FormControlsButtonComponent = function(componentElement) {
    var currentElementId;
    var actionName;
    var confirmation;
    var init = function() {
        currentElementId = window.currentElementId;
        actionName = componentElement.dataset.action;
        confirmation = componentElement.dataset.confirmation;
        if (actionName != null) {
            eventsManager.addHandler(componentElement, 'click', clickHandler);
        }
    };
    var clickHandler = function() {
        if (confirmation) {
            if (confirm(confirmation)) {
                sendRequest();
            }
        } else {
            sendRequest();
        }
    };
    var sendRequest = function() {
        var requestURL = window.currentElementURL + 'id:' + currentElementId + '/action:' + actionName;
        window.location.replace(requestURL);
    };
    init();
};