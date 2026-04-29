window.LinkListComponent = function(componentElement) {
    var init = function() {
        var elements;
        if (elements = _('.linklist_item_overlay_container', componentElement)) {
            for (var i = 0; i < elements.length; i++) {
                new LinkListItemThumbnailComponent(elements[i]);
            }
        }
    };
    init();
};

window.LinkListItemThumbnailComponent = function(componentElement) {
    var self = this;

    var init = function() {
        var overlayElment = _('.linklist_item_overlay', componentElement)[0];
        if (overlayElment) {
            var titleElement = _('.linklist_item_title', componentElement)[0];
            var contentElement = _('.linklist_item_content', componentElement)[0];
            var visibleOffset = 0;
            if (contentElement) {
                visibleOffset = componentElement.offsetHeight - contentElement.offsetHeight;
            }
            if (titleElement) {
                var titleHeight = titleElement.offsetHeight;
                if (visibleOffset < titleHeight) {
                    visibleOffset = titleHeight;
                }
            }

            self.initSlideOverlay({'overlayElement': overlayElment, 'visibleOffset': visibleOffset});
        }
    };
    init();
};
SlideOverlayMixin.call(LinkListItemThumbnailComponent.prototype);