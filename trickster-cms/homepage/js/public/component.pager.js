window.PagerComponent = function(componentElement) {
    var pagerData;
    this.getComponentElement = function() {
        return componentElement;
    };
    this.updateData = function(newData) {
        pagerData = newData;
        createDomStructure();
    };
    var createDomStructure = function() {
        var pagerButton;

        if (!componentElement) {
            componentElement = document.createElement('div');
            componentElement.className = 'pager_block';
        } else {
            while (componentElement.firstChild) {
                componentElement.removeChild(componentElement.firstChild);
            }
        }
        if (pagerData.pagesList.length > 1) {
            pagerButton = new PagerPreviousComponent(pagerData.previousPage, pagerData.callBack);
            componentElement.appendChild(pagerButton.getComponentElement());

            for (var i = 0; i < pagerData.pagesList.length; i++) {
                var pageData = pagerData.pagesList[i];
                var page = new PagerPageComponent(pageData, pagerData.callBack);
                componentElement.appendChild(page.getComponentElement());
            }
            pagerButton = new PagerNextComponent(pagerData.nextPage, pagerData.callBack);
            componentElement.appendChild(pagerButton.getComponentElement());

            componentElement.style.display = '';
        } else {
            componentElement.style.display = 'none';
        }
    };
};
window.PagerPageComponent = function(data, callBack) {
    var componentElement;
    var init = function() {
        if (data.active) {
            componentElement = document.createElement('a');
            componentElement.href = data.URL;
        } else {
            componentElement = document.createElement('span');
        }
        componentElement.className = 'pager_page';
        if (data.selected) {
            componentElement.className += ' pager_active';
        }
        componentElement.innerHTML = data.text;
        if (data.active) {
            eventsManager.addHandler(componentElement, 'click', click);
        }
    };
    var click = function(event) {
        event.preventDefault();
        if (callBack) {
            callBack(data.number);
        }
    };
    this.getComponentElement = function() {
        return componentElement;
    };
    init();
};
window.PagerPreviousComponent = function(data, callBack) {
    var componentElement;
    var init = function() {
        componentElement = document.createElement('a');
        componentElement.className = 'pager_previous';
        if (data.active) {
            componentElement.href = data.URL;
        } else {
            componentElement.href = '';
            componentElement.className += ' pager_hidden';
        }
        componentElement.innerHTML = data.text;
        if (data.active) {
            eventsManager.addHandler(componentElement, 'click', click);
        }
    };
    var click = function(event) {
        event.preventDefault();
        if (callBack) {
            callBack(data.number);
        }
    };
    this.getComponentElement = function() {
        return componentElement;
    };
    init();
};
window.PagerNextComponent = function(data, callBack) {
    var componentElement;
    var init = function() {
        componentElement = document.createElement('a');
        componentElement.className = 'pager_next';
        if (data.active) {
            componentElement.href = data.URL;
        } else {
            componentElement.href = '';
            componentElement.className += ' pager_hidden';
        }
        componentElement.innerHTML = data.text;
        if (data.active) {
            eventsManager.addHandler(componentElement, 'click', click);
        }
    };
    var click = function(event) {
        event.preventDefault();
        if (callBack) {
            callBack(data.number);
        }
    };
    this.getComponentElement = function() {
        return componentElement;
    };
    init();
};
