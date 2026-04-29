window.LinkListItemFormComponent = function(componentElement) {
    var self = this;
    var searchInputEl;

    var init = function() {
        createDomStructure();
        var types = searchInputEl.getAttribute('data-types');
        var apiMode = 'admin';

        new AjaxSelectComponent(searchInputEl, types, apiMode);
    };

    var createDomStructure = function() {
        searchInputEl = _('.linklistitem_form_search', componentElement)[0];
    };

    init();
};