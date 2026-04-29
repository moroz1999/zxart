window.GalleryDescriptionComponent = function(galleryInfo) {
    var componentElement, titleElement, descriptionElement;
    var self = this;

    var init = function() {
        createDomStructure();
        controller.addListener('galleryImageDisplay', onImageDisplay);
    };
    this.destroy = function() {
        controller.removeListener('galleryImageDisplay', onImageDisplay);
    };
    var createDomStructure = function() {
        componentElement = self.makeElement('div', 'gallery_description html_content gallery_description html_content');
        titleElement = self.makeElement('div', 'gallery_description_title', componentElement);
        descriptionElement = self.makeElement('div', 'gallery_descripion_description', componentElement);
    };
    var onImageDisplay = function(displayedImage) {
        if (displayedImage.getGallery() !== galleryInfo) {
            return;
        }
        if (galleryInfo.getDescriptionEffect() === 'opacity') {
            componentElement.style.opacity = 0;
            if (self.setDescription(displayedImage)) {
                window.setTimeout(fadeIn, 500);
            }
        } else {
            self.setDescription(displayedImage);
        }
    };
    var fadeIn = function() {
        TweenLite.to(componentElement, 1, {'css': {'opacity': 1}});
    };
    this.setDescription = function(imageInfo) {
        var displayed = false;
        var content;
        if (content = imageInfo.getTitle()) {
            titleElement.innerHTML = content;
            titleElement.style.display = 'block';
            displayed = true;
        } else {
            titleElement.style.display = 'none';
        }
        if (content = imageInfo.getDescription()) {
            descriptionElement.innerHTML = content;
            descriptionElement.style.display = 'block';
            displayed = true;
        } else {
            descriptionElement.style.display = 'none';
        }
        return displayed;
    };
    this.getComponentElement = function() {
        return componentElement;
    };
    init();
};
DomElementMakerMixin.call(GalleryDescriptionComponent.prototype);