window.ajaxSearchLogics = new function() {
    var self = this;
    var initComponents = function() {
        var elements;
        elements = _('.left_panel_ajaxsearch_block');
        for (var i = 0; i < elements.length; i++) {
            new HeaderAjaxSearchComponent(elements[i]);
        }
        elements = _('.ajaxitemsearch');
        for (var i = 0; i < elements.length; i++) {
            new AjaxItemSearchComponent(elements[i], {
                'apiMode': 'admin',
            });
        }
    };
    var receiveData = function(
        responseStatus, requestName, responseData, callBack) {
        if (responseStatus === 'success' && responseData) {
            callBack(responseData);
        } else {
            controller.fireEvent('ajaxSearchResultsFailure', responseData);
        }
    };
    this.sendQuery = function(
        callBack, query, types, apiMode, resultsLimit, language, filters) {
        var url = '/ajaxSearch/mode:' + apiMode + '/types:' + types + '/totals:' +
            1 + '/query:' + query;
        if (typeof resultsLimit !== 'undefined') {
            url += '/resultsLimit:' + parseInt(resultsLimit, 10);
        }
        if (typeof language !== 'undefined') {
            url += '/language:' + language;
        }
        if (typeof filters !== 'undefined') {
            url += '/filters:' + filters;
        }
        url += '/';
        var request = new JsonRequest(url,
            function(responseStatus, requestName, responseData) {
                return receiveData(responseStatus, requestName, responseData,
                    callBack);
            }, 'ajaxSearch');
        request.send();
    };
    controller.addListener('initDom', initComponents);
};