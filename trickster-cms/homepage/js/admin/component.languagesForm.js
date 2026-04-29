window.LanguagesFormComponent = function(componentElement) {
    var init = function() {
        var searchInputElements = _('.languages_form_searchinput', componentElement);
        for (var i = searchInputElements.length; i--;) {
            new AjaxSelectComponent(searchInputElements[i],
                'folder,news,production,service,newsList',
                'admin'
            );
        }
    };
    init();
};