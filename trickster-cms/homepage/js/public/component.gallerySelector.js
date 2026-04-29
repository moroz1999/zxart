window.GallerySelectorComponent = function(galleryInfo, imagesComponent) {
    var lastActiveThumbnailElement;
    var self = this;
    var componentElement;
    var centerElement;
    var lastTimeout;
    var init = function() {
        componentElement = document.createElement('div');
        componentElement.className = 'gallery_thumbnailsselector';

        centerElement = document.createElement('div');
        centerElement.className = 'gallery_thumbnailsselector_images';

        componentElement.appendChild(centerElement);

        var imagesInfoList = galleryInfo.getImagesList();
        for (var i = 0; i < imagesInfoList.length; i++) {
            var item = new GallerySelectorImageComponent(imagesInfoList[i], self);
            centerElement.appendChild(item.getComponentElement());
            if (i === 0) {
                var element = item.getComponentElement();
                if (element) {
                    element.classList.add('gallery_thumbnailsselector_active');
                    lastActiveThumbnailElement = element;
                }
            }
        }

        if (imagesInfoList.length > 3) {
            var leftButton = new GallerySelectorLeftComponent(self);
            componentElement.appendChild(leftButton.getComponentElement());

            var rightButton = new GallerySelectorRightComponent(self);
            componentElement.appendChild(rightButton.getComponentElement());
        }
        controller.addListener('galleryImageDisplay', updateEvent);

    };
    var updateEvent = function(image) {
        //check gallery ID in case there is more than one gallery on screen;
        if (image.getGallery().getId() == galleryInfo.getId()) {
            if (lastActiveThumbnailElement) {
                lastActiveThumbnailElement.classList.remove('gallery_thumbnailsselector_active');
            }

            var element = centerElement.querySelector('.gallery_thumbnailsselector_image_' + image.getId());
            if (element) {
                element.classList.add('gallery_thumbnailsselector_active');

                var scrollLeft = element.offsetLeft + (element.offsetWidth - centerElement.offsetWidth) / 2;
                if (scrollLeft < 0) {
                    scrollLeft = 0;
                } else if (scrollLeft > centerElement.scrollWidth - centerElement.offsetWidth) {
                    scrollLeft = centerElement.scrollWidth - centerElement.offsetWidth;
                }
                lastActiveThumbnailElement = element;
                TweenLite.to(centerElement, 2, {
                    'scrollLeft': scrollLeft,
                    'ease': Power2.easeOut,
                });
            }
        }
    };

    this.getComponentElement = function() {
        return componentElement;
    };

    this.scrollLeft = function() {
        galleryInfo.displayPreviousImage();
        galleryInfo.stopSlideShow();
    };

    this.scrollRight = function() {
        galleryInfo.displayNextImage();
        galleryInfo.stopSlideShow();
    };

    this.scrollStop = function() {
        if (lastTimeout) {
            if (window.cancelAnimationFrame) {
                window.cancelAnimationFrame(lastTimeout);
            } else {
                clearTimeout(lastTimeout);
            }
            lastTimeout = false;
        }
    };

    this.setSizes = function(width, height) {
        componentElement.style.height = height + 'px';
    };

    this.getGalleryHeight = function() {
        return componentElement.offsetHeight;
    };

    this.stopSlideShow = function() {
        galleryInfo.stopSlideShow();
    };

    init();
};

window.GallerySelectorImageComponent = function(imageInfo, parentComponent) {
    var componentElement;
    var mediaElement;
    var sourceElement;

    var init = function() {
        componentElement = document.createElement('div');
        componentElement.className = 'gallery_thumbnailsselector_image gallery_thumbnailsselector_image_' +
            imageInfo.getId();
        if (imageInfo.isVideo()) {
            componentElement.className += ' gallery_thumbnailsselector_video';
            mediaElement = document.createElement('video');
            mediaElement.autoplay = false;
            mediaElement.muted = true;
            componentElement.appendChild(mediaElement);
            sourceElement = document.createElement('source');
            sourceElement.type = 'video/mp4';
            sourceElement.src = imageInfo.getFileUrl();
            mediaElement.appendChild(sourceElement);
        } else {
            componentElement.style.backgroundImage = 'url(' +
                imageInfo.getThumbnailImageUrl() + ')';
        }

        window.eventsManager.addHandler(componentElement, 'click', clickHandler);
    };
    var clickHandler = function(e) {
        parentComponent.stopSlideShow();
        imageInfo.display();
    };
    this.getComponentElement = function() {
        return componentElement;
    };
    this.getId = function() {
        return imageInfo.getId();
    };
    init();
};
window.GallerySelectorLeftComponent = function(selectorObject) {
    var componentElement;
    var init = function() {
        componentElement = document.createElement('span');
        componentElement.className = 'gallery_thumbnailsselector_left';

        eventsManager.addHandler(componentElement, 'click', clickHandler);
    };

    var clickHandler = function(event) {
        selectorObject.scrollStop();
        selectorObject.scrollLeft();
    };

    this.getComponentElement = function() {
        return componentElement;
    };

    init();
};
window.GallerySelectorRightComponent = function(selectorObject) {
    var componentElement;
    var init = function() {
        componentElement = document.createElement('span');
        componentElement.className = 'gallery_thumbnailsselector_right';

        eventsManager.addHandler(componentElement, 'click', clickHandler);
    };

    var clickHandler = function(event) {
        selectorObject.scrollStop();
        selectorObject.scrollRight();
    };

    this.getComponentElement = function() {
        return componentElement;
    };

    init();
};

//todo: remove old gallery names in 06.2017
window.SlideGallerySelectorComponent = window.GallerySelectorComponent;
window.SlideGallerySelectorItemComponent = window.GallerySelectorImageComponent;
window.SlideGalleryLeftComponent = window.GallerySelectorLeftComponent;
window.SlideGalleryRightComponent = window.GallerySelectorRightComponent;

window.ScrollGallerySelectorComponent = window.GallerySelectorComponent;
window.ScrollGallerySelectorItemComponent = window.GallerySelectorImageComponent;
window.ScrollGalleryLeftComponent = window.GallerySelectorLeftComponent;
window.ScrollGalleryRightComponent = window.GallerySelectorRightComponent;