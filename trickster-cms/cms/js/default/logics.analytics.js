window.analyticsLogics = new function() {
    var init = function() {
        return;
        if (!window.newVisitor) {
            return;
        }
        if (!navigator.cookieEnabled) {
            return;
        }
        doXhr('Start', {referrer: document.referrer});
    };

    var doXhr = function(action, params) {
        var requestURL = window.rootURL + 'remote/action:' + action;
        var request = new JsonRequest(requestURL, null, null, params);
        request.send();
    };
    init();
};