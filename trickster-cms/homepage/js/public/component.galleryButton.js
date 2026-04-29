window.GalleryButtonComponent = function(imageInfo, galleryInfo) {
    var componentElement;
    var numberElement;
    var self = this;
    var number;

    var init = function() {
        number = imageInfo.getImageNumber();
        createDomStructure();
        eventsManager.addHandler(componentElement, 'click', onClick);
        controller.addListener('galleryImageDisplay', galleryImageDisplayHandler);

        if (number == 0) {
            self.activate();
        }
    };
    this.destroy = function() {
        eventsManager.removeHandler(componentElement, 'click', onClick);
        controller.removeListener('galleryImageDisplay', galleryImageDisplayHandler);
        if (componentElement.parentNode){
            componentElement.parentNode.removeChild(componentElement);
        }
    };
    var createDomStructure = function() {
        componentElement = self.makeElement('div', 'gallery_button');
        numberElement = self.makeElement('span', 'gallery_button_text');
        numberElement.innerHTML = "<small>" + number + 1 + "</small>";
        componentElement.appendChild(numberElement);
    };
    var onClick = function() {
        galleryInfo.stopSlideShow();
        imageInfo.display();
    };
    this.getComponentElement = function() {
        return componentElement;
    };
    var galleryImageDisplayHandler = function(displayedImage) {
        if (displayedImage.getGallery() != galleryInfo) {
            return;
        }
        if (imageInfo == displayedImage) {
            self.activate();
        } else if (displayedImage.getGallery() == galleryInfo) {
            self.deActivate();
        }
    };
    this.activate = function() {
        domHelper.addClass(componentElement, 'gallery_button_active');
    };
    this.deActivate = function() {
        domHelper.removeClass(componentElement, 'gallery_button_active');
    };
    init();
};
DomElementMakerMixin.call(GalleryButtonComponent.prototype);