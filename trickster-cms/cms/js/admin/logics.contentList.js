window.contentListLogics = new function() {
    var init = function() {
        var elements = _('.content_list_block');
        for (var i = elements.length; i--;) {
            new ContentListComponent(elements[i]);
        }
    };
    controller.addListener('initDom', init);
};