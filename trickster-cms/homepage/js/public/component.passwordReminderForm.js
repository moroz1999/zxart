function PasswordReminderFormComponent(componentElement) {
    var submit = function(event) {
        eventsManager.preventDefaultAction(event);
        componentElement.submit();
    };
    var init = function() {
        if (sendButton = _('.passwordreminder_submit', componentElement)[0]) {
            eventsManager.addHandler(sendButton, 'click', submit);
        }
    };
    var self = this;
    var sendButton = false;
    init();
}
