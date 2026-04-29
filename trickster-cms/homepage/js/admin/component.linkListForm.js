window.LinkListFormComponent = function(componentElement) {
    var self = this;
    var searchInputEl;

    var init = function() {
        searchInputEl = _('.linklist_form_search', componentElement)[0];
        var types = searchInputEl.getAttribute('data-types');
        var apiMode = 'admin';

        new AjaxSelectComponent(searchInputEl, types, apiMode);
    };
    init();
};