window.StaticGalleryComponent = function(componentElement, info) {
    var self = this;
    var fullScreenGallery;
    var initComponents = function() {
        var imagesList = info.getImagesList();
        if (imagesList.length > 0) {
            if (info.isFullScreenGalleryEnabled()) {
                fullScreenGallery = new FullScreenGalleryComponent(info);
                for (var i = 0; i < imagesList.length; i++) {
                    var imageElement = _('.galleryimageid_' + imagesList[i].getId(), componentElement)[0];
                    if (imageElement) {
                        new StaticGalleryImage(imageElement, imagesList[i], self);
                    }
                }
            }
        }
    };
    this.displayFullScreenGallery = function() {
        if (fullScreenGallery) {
            fullScreenGallery.display();
        }
    };
    controller.addListener('DOMContentReady', initComponents);
};
window.StaticGalleryImage = function(componentElement, imageObject, parentObject) {
    var self = this;

    var init = function() {
        var overlayElement = _('.gallery_details_item_overlay', componentElement)[0];
        var titleElement = _('.gallery_details_item_title', componentElement)[0];
        if (overlayElement) {
            self.initSlideOverlay({'overlayElement': overlayElement, 'visibleOffset': titleElement.offsetHeight});
        }
        eventsManager.addHandler(componentElement, 'click', clickHandler);
    };
    var clickHandler = function(event) {

        eventsManager.preventDefaultAction(event);
        imageObject.display();
        if (imageObject.getExternalLink()) {
            imageObject.openExternalLink();
        } else {
            parentObject.displayFullScreenGallery();
        }
    };
    init();
};
SlideOverlayMixin.call(StaticGalleryImage.prototype);