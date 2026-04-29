window.GalleryFullScreenButtonComponent = function(galleryInfo, imagesComponent) {
    var componentElement;
    var self = this;

    var init = function() {
        createDomStructure();
        eventsManager.addHandler(componentElement, 'click', onClick);
    };
    this.destroy = function() {
        eventsManager.removeHandler(componentElement, 'click', onClick);
    };
    var createDomStructure = function() {
        componentElement = self.makeElement('div', 'gallery_button_fullscreen');
        componentElement.innerHTML = window.translationsLogics.get('gallery.fullscreen');
    };
    var onClick = function() {
        galleryInfo.stopSlideShow();
        imagesComponent.displayFullScreenGallery();
    };
    this.getComponentElement = function() {
        return componentElement;
    };
    init();
};
DomElementMakerMixin.call(GalleryFullScreenButtonComponent.prototype);