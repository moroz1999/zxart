window.HeaderAjaxSearchComponent = function(componentElement) {
    var self = this;
    var init = function() {
        var inputElement = _('.ajaxsearch_input', componentElement)[0];
        if (inputElement) {
            var parameters = {
                'resultsLimit': 30,
                'position': 'fixed',
                'searchStringLimit': 0,
                'clickCallback': clickHandler,
                'apiMode': 'admin',
                'types': inputElement.getAttribute('data-types'),
            };

            new AjaxSearchComponent(inputElement, parameters);
        }
    };

    var clickHandler = function(data) {
        if (data.url) {
            document.location.href = data.url;
        }
    };

    init();
};