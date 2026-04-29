window.ContentFilterForm = function(componentElement) {
    var init = function() {
        if (inputElement = _('.content_filter_input', componentElement)[0]) {
            eventsManager.addHandler(inputElement, 'keyup', keyUpHandler);

            if (contentListElement = _('.content_list')[0]) {
                contentList = new ContentList(contentListElement);
            }
        }
    };
    var keyUpHandler = function() {
        window.clearTimeout(timeOut);
        var searchString = inputElement.value;
        if (searchString.length > 2) {
            timeOut = window.setTimeout(sendQuery, keyDelay);
        } else if (contentList) {
            contentList.displayAllContent();
        }
    };
    var sendQuery = function() {
        var searchString = inputElement.value;
        var URL = window.filterContentURL + 'search:' + searchString;

        artWebAjaxManager.makeRequest(URL, receiveData, false, true);
    };
    var receiveData = function(responseXML) {
        var elementsCount = 0;
        var elementsIdIndex = {};
        if (responseXML) {
            if (responseXML.documentElement) {
                if (responseXML.documentElement.childNodes) {
                    for (var i = 0; i < responseXML.documentElement.childNodes.length; i++) {
                        var resultItemElement = responseXML.documentElement.childNodes[i];
                        if (resultItemElement.tagName == 'id') {
                            if (resultItemElement.textContent) {
                                var id = parseInt(resultItemElement.textContent, 10);
                            } else {
                                var id = parseInt(resultItemElement.text, 10);
                            }
                            elementsIdIndex[id] = true;
                            elementsCount++;
                        }
                    }
                }
            }
        }
        if (contentList) {
            if (elementsCount > 0) {
                contentList.filterContent(elementsIdIndex);
            } else {
                contentList.displayAllContent();
            }
        }
    };
    var self = this;
    var inputElement = false;
    var contentList = false;
    var keyDelay = 400;
    var timeOut = false;

    init();
};
window.ContentList = function(componentElement) {
    this.filterContent = function(idIndex) {
        var contentList = _('.content_list_item', componentElement);
        for (var i = 0; i < contentList.length; i++) {
            var elementId = contentList[i].className.split('elementid_')[1];
            if (idIndex[elementId]) {
                contentList[i].style.display = 'table-row';
            } else {
                contentList[i].style.display = 'none';
            }
        }
    };
    this.displayAllContent = function() {
        var contentList = _('.content_list_item', componentElement);
        for (var i = 0; i < contentList.length; i++) {
            contentList[i].style.display = 'table-row';
        }
    };
    var self = this;
};