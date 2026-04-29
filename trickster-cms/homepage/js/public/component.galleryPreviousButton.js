window.GalleryPreviousButtonComponent = function(galleryObject) {
    var componentElement;
    var self = this;

    var init = function() {
        createDomStructure();
        eventsManager.addHandler(componentElement, 'click', onClick);
    };
    this.destroy = function() {
        eventsManager.removeHandler(componentElement, 'click', onClick);
        if (componentElement.parentNode){
            componentElement.parentNode.removeChild(componentElement);
        }
    };
    var createDomStructure = function() {
        componentElement = self.makeElement('div', 'gallery_button_navigation gallery_button_previous');
        componentElement.innerHTML = '<span class="gallery_button_text">' + window.translationsLogics.get('gallery.previous') + '</span>';
    };
    var onClick = function(event) {
        eventsManager.preventDefaultAction(event);
        eventsManager.cancelBubbling(event);
        galleryObject.stopSlideShow();
        galleryObject.displayPreviousImage();
    };
    this.getComponentElement = function() {
        return componentElement;
    };
    this.adjustHeight = function(height) {
        componentElement.style.height = height + 'px';
    };
    init();
};
DomElementMakerMixin.call(GalleryPreviousButtonComponent.prototype);