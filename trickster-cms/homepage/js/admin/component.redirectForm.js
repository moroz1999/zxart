window.RedirectFormComponent = function(componentElement) {
    var destinationInput;
    var init = function() {
        destinationInput = _('.redirect_destinationinput', componentElement)[0];

        var searchInputElement = _('.redirect_searchinput', componentElement)[0];
        var types = searchInputElement.getAttribute('data-types');
        var apiMode = 'public';

        new AjaxSelectComponent(searchInputElement, types, apiMode);
    };
    init();
};