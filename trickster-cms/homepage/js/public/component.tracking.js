window.trackingComponent = {
    emailClickEvent: function(email) {
        var postParameters = {
            'action': 'emailClick',
            'email': email,
        };
        var requestURL = window.rootURL + 'events/';
        var request = new JsonRequest(requestURL, null, null, postParameters);
        request.send();
    },
};