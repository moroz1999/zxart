window.GalleryPlaybackButtonComponent = function(galleryInfo) {
    var componentElement;
    var self = this;
    var playbackEnabled = true;

    var init = function() {
        createDomStructure();
        eventsManager.addHandler(componentElement, 'click', onClick);
        controller.addListener('gallerySlideShowUpdated', gallerySlideShowUpdatedHandler);
    };
    this.destroy = function() {
        eventsManager.removeHandler(componentElement, 'click', onClick);
        controller.removeListener('gallerySlideShowUpdated', gallerySlideShowUpdatedHandler);
    };
    var createDomStructure = function() {
        componentElement = self.makeElement('div', 'gallery_button gallery_button_pause');
    };
    var onClick = function() {
        if (playbackEnabled) {
            galleryInfo.stopSlideShow();
            playbackEnabled = false;
            updateStyle();
        } else {
            galleryInfo.startSlideShow();
            playbackEnabled = true;
            updateStyle();
        }
    };
    var gallerySlideShowUpdatedHandler = function(galleryId) {
        if (galleryId == galleryInfo.getId()) {
            playbackEnabled = galleryInfo.isSlideShowActive();
            updateStyle();
        }
    };
    var updateStyle = function() {
        if (!playbackEnabled) {
            domHelper.removeClass(componentElement, 'gallery_button_pause');
            domHelper.addClass(componentElement, 'gallery_button_play');
        } else {
            domHelper.removeClass(componentElement, 'gallery_button_play');
            domHelper.addClass(componentElement, 'gallery_button_pause');
        }
    };
    this.getComponentElement = function() {
        return componentElement;
    };
    init();
};
DomElementMakerMixin.call(GalleryPlaybackButtonComponent.prototype);