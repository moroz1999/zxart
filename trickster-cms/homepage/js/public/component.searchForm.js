function SearchFormComponent(formElement, displayInElement, displayTotals, totalsElement, position) {
    var self = this;

    this.submitButton = null;
    this.formElement = null;
    var inputElement;

    var init = function() {
        self.formElement = formElement;
        if (self.submitButton = formElement.querySelector('.search_button')) {
            eventsManager.addHandler(self.submitButton, 'click', self.submitForm);
        }
        eventsManager.addHandler(self.formElement, 'submit', self.submitForm);
        eventsManager.addHandler(self.formElement, 'keydown', self.checkKey);

        var allowedSearchTypes;
        if (formElement.dataset.types && formElement.dataset.types != '') {
            allowedSearchTypes = formElement.dataset.types;
        } else {
            allowedSearchTypes = 'product,category,news,article,folder,discount';
        }

        var showedSearchElementComponents;
        if (formElement.dataset.showed && formElement.dataset.showed != '') {
            showedSearchElementComponents = formElement.dataset.showed;
        } else {
            showedSearchElementComponents = 'title';
        }

        inputElement = formElement.querySelector('.ajaxsearch_input');
        if (inputElement && inputElement.className.indexOf('ajaxsearch_input') != -1) {
            var parameters = {
                'clickCallback': ajaxSearchResultClick,
                'apiMode': 'public',
                'totalsElement': totalsElement,
                'position': position,
                'searchStringLimit': 1,
                'types': allowedSearchTypes,
                'showedElementComponents': showedSearchElementComponents,
            };
            if (typeof displayInElement != 'undefined') {
                parameters.displayInElement = displayInElement;
            }
            if (typeof displayTotals != 'undefined') {
                parameters.displayTotals = displayTotals;
            }
            if (typeof position != 'undefined') {
                parameters.position = position;
            }
            new AjaxSearchComponent(inputElement, parameters);
        }

    };

    var ajaxSearchResultClick = function(data) {
        if (data.url) {
            document.location.href = data.url;
        }
    };

    this.checkKey = function(event) {
        if (event.keyCode == 13) {
            self.submitForm();
        }
    };

    this.submitForm = function(event) {
        if (event) {
            eventsManager.preventDefaultAction(event);
        }
        var targetUrl = self.formElement.getAttribute('action');
        if (inputElement.value) {
            targetUrl += 'phrase:' + encodeURIComponent(inputElement.value.replace('/', '%s%')) + '/';
        }
        document.location.href = targetUrl;
    };
    controller.addListener('DOMContentReady', init);
}
