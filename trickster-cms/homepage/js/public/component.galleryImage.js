window.GalleryImageComponent = function(imageInfo, parentObject, galleryInfo) {
    var self = this;

    this.title = null;
    this.link = null;
    this.preloaded = false;

    var mediaOriginalWidth;
    var mediaOriginalHeight;
    var galleryWidth;
    var galleryHeight;

    var componentElement;
    var mediaElement;
    var sourceElement;
    var infoElement;
    var clickable = false;
    var videoLoadStarted = false;
    var autoStart = false;
    var init = function() {
        createDomStructure();
        clickable = (parentObject.hasFullScreenGallery() ||
            imageInfo.getExternalLink() || imageInfo.isVideo());
        if (clickable) {
            componentElement.className += ' gallery_image_clickable';
            eventsManager.addHandler(componentElement, eventsManager.getPointerStartEventName(), touchStart);
        }
        if (typeof parentObject.videoAutoStart != 'undefined') {
            if (parentObject.videoAutoStart()) {
                autoStart = true;
            }
        }
        controller.addListener('galleryImageDisplay', displayHandler);
        controller.addListener('mobileBreakpointChanged', mobileBreakpointCallback);
    };

    var mobileBreakpointCallback = function() {
        createMediaElement();
        self.preloaded = false;
        self.checkPreloadImage(function() {
            componentElement.style.display = '';
        });
    };


    var touchStart = function(event) {
        //ignore right mouse click
        if (typeof event.which === 'undefined' || event.which !== 3) {
            eventsManager.removeHandler(componentElement, eventsManager.getPointerStartEventName(), touchStart);
            eventsManager.addHandler(componentElement, eventsManager.getPointerEndEventName(), touchEnd);
            eventsManager.addHandler(componentElement, eventsManager.getPointerMoveEventName(), touchMove);
        }
    };

    var touchMove = function(event) {
        resetTouchiness();
    };

    var touchEnd = function(event) {
        resetTouchiness();
        if (imageInfo.getExternalLink()) {
            imageInfo.openExternalLink();
        } else {
            parentObject.displayFullScreenGallery();
        }
    };

    var resetTouchiness = function() {
        eventsManager.removeHandler(componentElement, eventsManager.getPointerEndEventName(), touchEnd);
        eventsManager.removeHandler(componentElement, eventsManager.getPointerMoveEventName(), touchMove);
        eventsManager.addHandler(componentElement, eventsManager.getPointerStartEventName(), touchStart);
    };
    this.destroy = function() {
        eventsManager.removeHandler(componentElement,
            eventsManager.getPointerStartEventName(), touchStart);
        eventsManager.removeHandler(componentElement,
            eventsManager.getPointerEndEventName(), touchEnd);
        eventsManager.removeHandler(componentElement,
            eventsManager.getPointerMoveEventName(), touchMove);
    };
    var createDomStructure = function() {
        componentElement = document.createElement('div');
        componentElement.className = 'gallery_image';
        componentElement.style.display = 'none';

        createMediaElement();
        var description = imageInfo.getDescription();
        var title = imageInfo.getTitle();
        if (galleryInfo.getDescriptionType() === 'overlay' && (description || title)) {
            infoElement = self.makeElement('div', 'gallery_image_info gallery_description html_content', componentElement);
            if (title) {
                var titleElement = self.makeElement('div', 'gallery_image_title gallery_description_title', infoElement);
                titleElement.innerHTML = title;
            }
            if (description) {
                var descriptionElement = self.makeElement('div', 'gallery_image_description gallery_descripion_description', infoElement);
                descriptionElement.innerHTML = description;
            }
            if (galleryInfo.getDescriptionEffect() === 'none') {
                showInfo();
            }
        }
    };
    var createMediaElement = function() {
        if (mediaElement) {
            componentElement.removeChild(mediaElement);
        }
        if (imageInfo.isVideo()) {
            self.checkPreloadImage = checkPreloadVideo;

            mediaElement = document.createElement('video');
            mediaElement.loop = true;
            mediaElement.muted = true;
            mediaElement.setAttribute('webkit-playsinline', 'webkit-playsinline');
            mediaElement.setAttribute('playsinline', 'playsinline');
            mediaElement.style.visibility = 'hidden';
            componentElement.appendChild(mediaElement);
            sourceElement = document.createElement('source');
            sourceElement.type = 'video/mp4';
            sourceElement.src = imageInfo.getFileUrl();
            mediaElement.appendChild(sourceElement);
        } else {
            self.checkPreloadImage = checkPreloadImage;

            mediaElement = document.createElement('img');
            mediaElement.style.visibility = 'hidden';
            componentElement.appendChild(mediaElement);
        }
    };
    var showInfo = function() {
        domHelper.addClass(infoElement, 'gallery_image_info_visible');
        TweenLite.to(infoElement, 0.5, {'css': {'opacity': 1}});
    };

    var hideInfo = function() {
        TweenLite.to(infoElement, 0.25, {
            'css': {'opacity': 0}, 'onComplete': function() {
                domHelper.removeClass(infoElement, 'gallery_image_info_visible');
            },
        });
    };

    this.checkPreloadImage = null;

    var checkPreloadImage = function(callBack) {
        if (!mediaElement.src) {
            mediaElement.srcset = imageInfo.getBigImageSrcSet(window.mobileLogics.isPhoneActive());
            mediaElement.src = imageInfo.getBigImageUrl(window.mobileLogics.isPhoneActive());

            mediaElement.style.visibility = 'hidden';
            componentElement.style.display = '';
        }
        if (!mediaElement.complete) {
            window.setTimeout(function(callBack) {
                return function() {
                    self.checkPreloadImage(callBack);
                };
            }(callBack), 100);
        } else {
            if (!self.preloaded) {
                mediaElement.style.visibility = 'visible';
                mediaOriginalWidth = mediaElement.offsetWidth;
                mediaOriginalHeight = mediaElement.offsetHeight;
                componentElement.style.display = 'none';
                self.preloaded = true;
                resizeImageElement();
            }

            if (callBack) {
                callBack();
            }
        }
    };

    var checkPreloadVideo = function(callBack) {
        if (mediaElement.readyState < 3) {
            if (!videoLoadStarted) {
                videoLoadStarted = true;
                var promise = mediaElement.load();
            }
            window.setTimeout(function(callBack) {
                return function() {
                    self.checkPreloadImage(callBack);
                };
            }(callBack), 100);
        } else {
            if (!self.preloaded) {
                mediaElement.style.visibility = 'visible';
                mediaOriginalWidth = mediaElement.videoWidth;
                mediaOriginalHeight = mediaElement.videoHeight;

                componentElement.style.display = 'none';
                self.preloaded = true;
                resizeImageElement();
            }
            if (callBack) {
                callBack();
            }
        }

    };

    var displayHandler = function(newImage) {
        if (imageInfo.isVideo()) {
            if (newImage.getId() === imageInfo.getId()) {
                checkPreloadVideo(videoPlayCallback);
            } else {
                mediaElement.pause();
            }
        }
        if (galleryInfo.getDescriptionEffect() === 'opacity') {
            if (newImage.getId() === imageInfo.getId()) {
                showInfo();
            } else {
                hideInfo();
            }
        }
    };
    var videoPlayCallback = function() {
        mediaElement.play();
    };
    this.resize = function(imagesContainerWidth, imagesContainerHeight) {
        galleryWidth = imagesContainerWidth;
        galleryHeight = imagesContainerHeight;

        resizeImageElement();
    };

    var resizeImageElement = function() {
        if (galleryWidth && galleryHeight) {
            componentElement.style.width = galleryWidth + 'px';
            componentElement.style.height = galleryHeight + 'px';

            if (mediaOriginalWidth && mediaOriginalHeight) {
                var imageWidth, imageHeight;
                var positionTop = 0, positionLeft = 0;

                var logic = imageInfo.getImageResizeType(window.mobileLogics.isPhoneActive());
                var aspectRatio = mediaOriginalWidth / mediaOriginalHeight;
                if (logic === 'fill') {
                    imageHeight = galleryHeight;
                    imageWidth = imageHeight * aspectRatio;
                    if (imageWidth < galleryWidth) {
                        imageWidth = galleryWidth;
                        imageHeight = imageWidth / aspectRatio;
                    }
                    // centering
                    if (imageHeight > galleryHeight) {
                        positionTop = (imageHeight - galleryHeight) / -2;
                    }
                    if (imageWidth > galleryWidth) {
                        positionLeft = (imageWidth - galleryWidth) / -2;
                    }
                }
                else if (logic === 'contain') {
                    imageHeight = galleryHeight;
                    imageWidth = imageHeight * aspectRatio;
                    if (imageWidth > galleryWidth) {
                        imageWidth = galleryWidth;
                        imageHeight = imageWidth / aspectRatio;
                    }
                    // centering
                    if (imageHeight > galleryHeight) {
                        positionTop = (imageHeight - galleryHeight) / -2;
                    }
                    if (imageWidth > galleryWidth) {
                        positionLeft = (imageWidth - galleryWidth) / -2;
                    }
                } else {
                    imageWidth = mediaOriginalWidth;
                    imageHeight = mediaOriginalHeight;

                    if (imageWidth > galleryWidth) {
                        imageWidth = galleryWidth;
                        imageHeight = imageWidth / aspectRatio;
                    }

                    if (imageHeight > galleryHeight) {
                        imageHeight = galleryHeight;
                        imageWidth = imageHeight * aspectRatio;
                    }
                    positionTop = (galleryHeight - imageHeight) / 2;
                    positionLeft = (galleryWidth - imageWidth) / 2;
                }
                if (mediaElement && self.preloaded) {
                    mediaElement.style.width = imageWidth + 'px';
                    mediaElement.style.height = imageHeight ? imageHeight + 'px' : '';
                    mediaElement.style.left = positionLeft + 'px';
                    mediaElement.style.top = positionTop + 'px';
                }
            }
        }
    };

    this.getComponentElement = function() {
        return componentElement;
    };
    this.getImageElement = function() {
        return mediaElement;
    };
    this.getId = function() {
        return imageInfo.getId();
    };

    this.activate = function() {
        imageInfo.display();
    };
    this.getImageInfo = function() {
        return imageInfo;
    };
    init();
};
DomElementMakerMixin.call(GalleryImageComponent.prototype);