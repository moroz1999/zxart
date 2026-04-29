window.JsonRequest = function(requestURL, callback, requestName, requestParameters, formData) {
    var self = this;

    var responseData = false;
    var responseStatus = false;
    this.send = function() {
        if (requestURL) {
            var parameters = {};

            parameters['requestURL'] = requestURL;
            parameters['successCallBack'] = successCallBack;
            parameters['failCallBack'] = failCallBack;
            parameters['requestXML'] = false;
            parameters['requestType'] = 'POST';
            parameters['postParameters'] = requestParameters;
            parameters['formData'] = formData;

            ajaxManager.makeRequest(parameters);
        }
    };
    var successCallBack = function(responseText) {
        var parsedData;

        responseStatus = 'invalid';
        responseData = {};

        if (typeof responseText !== 'undefined' && responseText !== '') {
            if (parsedData = JSON.parse(responseText)) {
                if (typeof (parsedData.responseStatus !== 'undefined') && typeof (parsedData.responseData !== 'undefined')) {
                    responseStatus = parsedData.responseStatus;
                    responseData = parsedData.responseData;
                }
            }
        }
        if (self.responseStatus == 'success') {
            controller.fireEvent('requestSuccess');
        }
        if (self.responseStatus == 'fail') {
            controller.fireEvent('requestFail', parsedData);
        }
        if (responseData) {
            deliverResponse();
        } else {
            failCallBack();
        }
    };
    var failCallBack = function() {
        responseStatus = 'invalid';
        responseData = {};
        deliverResponse();
    };
    var deliverResponse = function() {
        if (typeof callback == 'function') {
            callback(responseStatus, requestName, responseData);
        }
    };
    this.setRequestParameters = function(newRequestParameters) {
        requestParameters = newRequestParameters;
    };
};