window.ajaxManager = new function() {
    this.makeRequest = function(parameters) {
        if (typeof parameters == 'object') {
            var ajaxRequestObject = new AjaxRequest(parameters);
            ajaxRequestObject.makeRequest();
        }
    };
};
window.AjaxRequest = function(parameters) {
    var status = null;

    var failureDelay = 20000;
    var failureCheckTimeOut = null;

    var XMLHttpResource = null;

    var requestURL = false;
    var requestXML = false;
    var requestType = false;
    var contentType = false;
    var successCallBack = false;
    var failCallBack = false;
    var progressCallBack = false;
    var postParameters = false;
    var overrideMimeType = false;
    var getParameters = false;
    var formData = false;

    var init = function() {
        if (typeof parameters.requestXML !== 'undefined') {
            requestXML = parameters.requestXML;
        } else {
            requestXML = false;
        }

        if (typeof parameters.requestURL !== 'undefined') {
            requestURL = parameters.requestURL;
        } else {
            requestURL = false;
        }

        if (typeof parameters.requestType !== 'undefined') {
            requestType = parameters.requestType.toUpperCase();
        } else {
            requestType = 'POST';
        }

        if (typeof parameters.contentType !== 'undefined') {
            contentType = parameters.contentType;
        } else {
            contentType = 'multipart/form-data';
        }

        if (typeof parameters.postParameters === 'object') {
            postParameters = parameters.postParameters;
        } else {
            postParameters = {};
        }

        if (typeof parameters.getParameters === 'object') {
            getParameters = parameters.getParameters;
        } else {
            getParameters = {};
        }

        if (typeof parameters.successCallBack === 'function') {
            successCallBack = parameters.successCallBack;
        } else {
            successCallBack = false;
        }
        if (typeof parameters.failureDelay !== 'undefined') {
            failureDelay = parameters.failureDelay;
        }
        if (typeof parameters.formData !== 'undefined') {
            formData = parameters.formData;
        }
        if (typeof parameters.overrideMimeType !== 'undefined') {
            overrideMimeType = parameters.overrideMimeType;
        }
        if (typeof parameters.failCallBack === 'function') {
            failCallBack = parameters.failCallBack;
        } else {
            failCallBack = false;
        }
        if (typeof parameters.progressCallBack === 'function') {
            progressCallBack = parameters.progressCallBack;
        } else {
            progressCallBack = false;
        }

        XMLHttpResource = getXMLHttpRequestObject();
    };
    this.makeRequest = function() {
        if (requestType === 'POST') {
            if (formData) {
                sendRequest();
            } else {
                var converter = new AjaxRequestDataConverter(postParameters, sendRequest, contentType);
                converter.preparePostData();
            }
        } else {
            if (typeof getParameters === 'object') {
                var urlParameters = '';
                for (var name in getParameters) {
                    var value = getParameters[name];
                    urlParameters += name + '=' + value + '&';
                }

                if (urlParameters && (requestURL.indexOf('?') === -1)) {
                    requestURL += '?';
                }
            }
            sendRequest();
        }
    };
    var sendRequest = function(headers, postBody) {
        if (progressCallBack) {
            window.eventsManager.addHandler(XMLHttpResource.upload, 'progress', progressCallBack);
        }
        if (overrideMimeType && XMLHttpResource.overrideMimeType) {
            XMLHttpResource.overrideMimeType(overrideMimeType);
        }
        if (failureDelay !== false) {
            failureCheckTimeOut = window.setTimeout(requestTimeOutHandler, failureDelay);
        }
        if (requestType === 'POST') {
            XMLHttpResource.open('POST', requestURL, true);
            for (var header in headers) {
                XMLHttpResource.setRequestHeader(header, headers[header]);
            }
            XMLHttpResource.onreadystatechange = catchRequestAnswer;
            if (formData) {
                XMLHttpResource.send(formData);
            } else if (typeof postBody !== undefined) {
                XMLHttpResource.send(postBody);
            }
        } else {
            XMLHttpResource.open('GET', requestURL, true);
            XMLHttpResource.onreadystatechange = catchRequestAnswer;
            if (formData) {
                XMLHttpResource.send(formData);
            } else {
                XMLHttpResource.send();
            }
        }
    };
    var catchRequestAnswer = function() {
        if (status == null) {
            if (XMLHttpResource.readyState === 4) {
                window.clearTimeout(failureCheckTimeOut);
                if (XMLHttpResource.status === 200) {
                    status = 'success';

                    if (successCallBack) {
                        var callBackArgument = false;
                        if (requestXML) {
                            callBackArgument = XMLHttpResource.responseXML;
                        } else {
                            callBackArgument = XMLHttpResource.responseText;
                        }
                        successCallBack(callBackArgument);
                    }
                } else {
                    status = 'failure';
                    if (failCallBack) {
                        failCallBack();
                    }
                }
            }
        }
    };
    var requestTimeOutHandler = function() {
        status = 'timeout';
        if (failCallBack) {
            failCallBack();
        }
    };

    var formatNumber = function(number, decimals) {
        number = number.toString();
        if (number.length < decimals) {
            for (var a = decimals - number.length; a > 0; a--) {
                number = '0' + number;
            }
        }
        return number;
    };

    var getXMLHttpRequestObject = function() {
        var result = false;
        if (window.XMLHttpRequest) {
            result = new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            try {
                result = new ActiveXObject('Msxml2.XMLHTTP');
            } catch (e) {
                try {
                    result = new ActiveXObject('Microsoft.XMLHTTP');
                } catch (E) {
                    result = false;
                }
            }
        }

        return result;
    };

    init();
};
window.AjaxRequestDataConverter = function(postParameters, callBack, contentType) {
    this.preparePostData = function() {
        if (contentType === 'multipart/form-data' && typeof window.FormData === 'undefined') {
            for (var name in postParameters) {
                var value = postParameters[name];
                if (typeof value === 'object' && value.name && value.size && value.type) {
                    fileContents[name] = false;

                    var reader = new FileReader();
                    reader.onload = function(fieldName) {
                        return function(event) {
                            getFileContents(event, fieldName);
                        };
                    }(name);
                    reader.readAsBinaryString(value);
                }
            }
            checkFilesPreload();
        } else {
            generateContentBody();
        }
    };
    var getFileContents = function(event, name) {
        fileContents[name] = event.target.result;
        checkFilesPreload();
    };
    var checkFilesPreload = function() {
        var loaded = true;
        for (var i in fileContents) {
            if (fileContents[i] === false) {
                loaded = false;
                break;
            }
        }
        if (loaded) {
            generateContentBody();
        }
    };
    var generateContentBody = function() {
        var contentBody = '';
        var headers = {};
        if (contentType === 'application/x-www-form-urlencoded') {
            for (var name in postParameters) {
                contentBody = contentBody + name + '=' + postParameters[name] + '&';
            }
            contentBody = encodeURI(contentBody);
            headers['Content-type'] = 'application/x-www-form-urlencoded';
        } else if (contentType === 'multipart/form-data') {
            if (typeof window.FormData !== 'undefined') {
                contentBody = new FormData();
                for (var name in postParameters) {
                    contentBody.append(name, postParameters[name]);
                }
            } else {
                var boundary = '---------------------------';
                boundary += Math.floor(Math.random() * 32768);
                boundary += Math.floor(Math.random() * 32768);
                boundary += Math.floor(Math.random() * 32768);

                headers['Content-type'] = 'multipart/form-data; boundary=' + boundary;

                for (var name in postParameters) {
                    var value = postParameters[name];
                    if (typeof value === 'object' && value.name && value.size && value.type) {
                        contentBody += '--' + boundary + '\r\n' + 'Content-Disposition: form-data; name="' + name + '"; filename="' + value.name + '"';
                        contentBody += '\r\n' + 'Content-Type: "' + value.type + '"';
                        contentBody += '\r\n\r\n';
                        contentBody += fileContents[name];
                        contentBody += '\r\n';
                    } else {
                        contentBody += '--' + boundary + '\r\n' + 'Content-Disposition: form-data; name="' + name + '"';
                        contentBody += '\r\n\r\n';
                        contentBody += value;
                        contentBody += '\r\n';
                    }
                }
                contentBody += '--' + boundary + '--';
            }
        }
        callBack(headers, contentBody);
    };
    var fileContents = {};
};