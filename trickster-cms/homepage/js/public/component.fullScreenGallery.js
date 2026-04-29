window.FullScreenGalleryComponent = function(galleryObject) {
    var self = this;

    var displayed = false;
    var domCreated = false;

    var componentElement;
    var centerComponent;

    var init = function() {
        var imagesInfoList = galleryObject.getImagesList();
        if (imagesInfoList.length) {
            controller.addListener('galleryImageDisplay', galleryImageDisplayHandler);
        }
    };
    this.display = function() {
        if (!domCreated) {
            createDomStructure();
        }
        if (!displayed) {
            displayed = true;
            DarkLayerComponent.showLayer(null, displayComponent);
        }
    };
    var displayComponent = function() {
        componentElement.style.display = 'block';
        centerComponent.updateContents();
        window.addEventListener('keydown', keyDownHandler);
    };
    var keyDownHandler = function(event) {
        if (event.keyCode == '27') {
            eventsManager.preventDefaultAction(event);
            self.hideComponent();
        }
        if (event.keyCode == '32' || event.keyCode == '39') {
            eventsManager.preventDefaultAction(event);
            galleryObject.displayNextImage();
        }
        if (event.keyCode == '8' || event.keyCode == '37') {
            eventsManager.preventDefaultAction(event);
            galleryObject.displayPreviousImage();
        }
    };
    var galleryImageDisplayHandler = function(imageObject) {
        if (displayed) {
            centerComponent.setCurrentImage(imageObject);
        }
    };
    var createDomStructure = function() {
        domCreated = true;

        componentElement = document.createElement('div');
        componentElement.className = 'fullscreen_gallery_block';
        componentElement.style.display = 'none';
        eventsManager.addHandler(componentElement, 'click', self.hideComponent);

        centerComponent = new FullScreenGalleryCenterComponent(self, galleryObject);
        componentElement.appendChild(centerComponent.componentElement);

        document.body.appendChild(componentElement);
    };
    this.hideComponent = function() {
        if (displayed) {
            window.removeEventListener('keydown', keyDownHandler);

            displayed = false;
            componentElement.style.display = 'none';
            centerComponent.hideCurrentImage();

            DarkLayerComponent.hideLayer();
        }
    };
    init();
};
window.FullScreenGalleryCenterComponent = function(
    galleryComponent, galleryObject) {
    var self = this;
    this.componentElement = null;

    var currentImageComponent;

    var closeButtonComponent;
    var nextButtonComponent;
    var prevButtonComponent;
    var imagesElement;

    var imagesObjectsList;

    var heightCoeff = 0.9;
    var widthCoeff = 0.9;

    var oldImageComponent;
    var imagesComponentsList;
    var imagesComponentsIndex;

    var init = function() {
        imagesObjectsList = galleryObject.getImagesList();
        createDomStructure();
    };
    var createDomStructure = function() {
        self.componentElement = document.createElement('div');
        self.componentElement.className = 'fullscreen_gallery_center';

        eventsManager.addHandler(self.componentElement, 'click', clickHandler);
        eventsManager.addHandler(self.componentElement, 'mousedown',
            mouseDownHandler);

        imagesElement = document.createElement('div');
        imagesElement.className = 'fullscreen_gallery_images';
        self.componentElement.appendChild(imagesElement);

        imagesComponentsList = [];
        imagesComponentsIndex = {};

        for (var i = 0; i < imagesObjectsList.length; i++) {
            var imageComponent = new FullScreenGalleryImageComponent(
                imagesObjectsList[i], self, galleryObject.getDescriptionType());
            imagesComponentsList.push(imageComponent);
            imagesComponentsIndex[imagesObjectsList[i].getId()] = imageComponent;

            imagesElement.appendChild(imageComponent.getComponentElement());
        }

        if (imagesObjectsList.length > 1) {
            nextButtonComponent = new FullScreenGalleryNextComponent(galleryObject);
            prevButtonComponent = new FullScreenGalleryPrevComponent(galleryObject);
            self.componentElement.appendChild(nextButtonComponent.componentElement);
            self.componentElement.appendChild(prevButtonComponent.componentElement);
        }
        closeButtonComponent = new FullScreenGalleryCloseComponent(galleryComponent);
        self.componentElement.appendChild(closeButtonComponent.componentElement);
    };
    this.updateContents = function() {
        var imageObject = galleryObject.getCurrentImage();
        var currentImageId = imageObject.getId();
        if (typeof imagesComponentsIndex[currentImageId] !== 'undefined') {
            currentImageComponent = imagesComponentsIndex[currentImageId];
            updateButtonsStatus();
            if (currentImageComponent) {
                currentImageComponent.checkPreloadImage(function() {
                    resize();
                    currentImageComponent.display();
                });
            }
        }
    };
    this.hideCurrentImage = function() {
        if (currentImageComponent) {
            currentImageComponent.hide();
        }
    };
    this.setCurrentImage = function(imageObject) {
        var imageId = imageObject.getId();
        if (!currentImageComponent || imageId != currentImageComponent.id) {
            if (typeof imagesComponentsIndex[imageId] !== 'undefined') {
                if (currentImageComponent) {
                    oldImageComponent = currentImageComponent;
                }

                currentImageComponent = imagesComponentsIndex[imageId];

                if (oldImageComponent) {
                    oldImageComponent.hide();
                }
                self.updateContents();
            }
        }
    };
    var resize = function() {
        var viewPortWidth = window.innerWidth
            ? window.innerWidth
            : document.documentElement.offsetWidth;
        var viewPortHeight = window.innerHeight
            ? window.innerHeight
            : document.documentElement.offsetHeight;

        var elementWidth = viewPortWidth * widthCoeff;
        var elementHeight = viewPortHeight * heightCoeff;

        imagesElement.style.width = elementWidth + 'px';
        imagesElement.style.height = elementHeight + 'px';

        var positionLeft = (viewPortWidth - self.componentElement.offsetWidth) / 2;
        var positionTop = (viewPortHeight - self.componentElement.offsetHeight) / 2;

        self.componentElement.style.left = positionLeft + 'px';
        self.componentElement.style.top = positionTop + 'px';

        if (nextButtonComponent) {
            nextButtonComponent.adjustHeight(imagesElement);
        }
        if (prevButtonComponent) {
            prevButtonComponent.adjustHeight(imagesElement);
        }

        currentImageComponent.resize();
    };
    var updateButtonsStatus = function() {
        if (nextButtonComponent) {
            if (!galleryObject.getNextImage()) {
                nextButtonComponent.componentElement.style.display = 'none';
            } else {
                nextButtonComponent.componentElement.style.display = 'block';
            }
        }
        if (prevButtonComponent) {
            if (!galleryObject.getPrevImage()) {
                prevButtonComponent.componentElement.style.display = 'none';
            } else {
                prevButtonComponent.componentElement.style.display = 'block';
            }
        }

    };
    var clickHandler = function(event) {
        eventsManager.preventDefaultAction(event);
        eventsManager.cancelBubbling(event);
    };
    var mouseDownHandler = function(event) {
        eventsManager.preventDefaultAction(event);
        eventsManager.cancelBubbling(event);
    };

    init();
};

window.FullScreenGalleryImageComponent = function(
    imageInfo, parentObject, descriptionType) {
    var self = this;
    var mediaOriginalWidth;
    var mediaOriginalHeight;
    var videoLoadStarted;
    this.preloaded = false;

    this.id = null;
    var componentElement;
    var mediaElement;
    var sourceElement;

    var init = function() {
        self.id = imageInfo.getId();

        componentElement = document.createElement('div');
        componentElement.style.display = 'none';
        componentElement.className = 'fullscreen_gallery_image';

        if (imageInfo.isVideo()) {
            self.checkPreloadImage = checkPreloadVideo;

            mediaElement = document.createElement('video');
            mediaElement.loop = true;
            mediaElement.muted = true;
            mediaElement.controls = false;
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

        if (descriptionType === 'overlay' &&
            (imageInfo.getDescription() || imageInfo.getTitle())) {

            var overlayElment = document.createElement('div');
            overlayElment.className = 'gallery_details_item_overlay';

            var info;
            if (info = imageInfo.getTitle()) {
                var titleElement = document.createElement('div');
                titleElement.className = 'gallery_details_item_title';
                titleElement.innerHTML = info;
                overlayElment.appendChild(titleElement);
            }
            if (info = imageInfo.getDescription()) {
                var descriptionElement = document.createElement('div');
                descriptionElement.className = 'gallery_details_item_description';
                descriptionElement.innerHTML = info;
                overlayElment.appendChild(descriptionElement);
            }
            componentElement.appendChild(overlayElment);

            var visibleOffset = titleElement ? titleElement.offsetHeight : 0;
            self.initSlideOverlay({
                'overlayElement': overlayElment,
                'overlayParentElement': parentObject.componentElement,
                'enableMouseover': false,
                'visibleOffset': visibleOffset,
            });
        }

    };
    this.getComponentElement = function() {
        return componentElement;
    };
    this.display = function() {
        componentElement.style.opacity = 0;
        componentElement.style.display = 'block';
        TweenLite.to(componentElement, 1, {'css': {'opacity': 1}});

        if (imageInfo.isVideo()) {
            mediaElement.play();
        }
    };
    this.checkPreloadImage = null;

    var checkPreloadImage = function(callBack) {
        if (!mediaElement.src) {
            mediaElement.src = imageInfo.getFullImageUrl();
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
                self.resize();
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
                mediaElement.load();
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
                self.resize();
            }
            if (callBack) {
                callBack();
            }
        }
    };

    this.hide = function(callBack) {
        if (imageInfo.isVideo()) {
            mediaElement.pause();
        }
        TweenLite.to(componentElement, 1, {
            'css': {'opacity': 0}, 'onComplete': function(callBack) {
                return function() {
                    finishHide(callBack);
                };
            }(callBack),
        });
    };
    var finishHide = function(callBack) {
        componentElement.style.display = 'none';
        if (typeof callBack != 'undefined') {
            callBack();
        }
    };
    this.resize = function() {
        var aspectRatio = mediaOriginalWidth / mediaOriginalHeight;

        var imageWidth = mediaOriginalWidth;
        var imageHeight = mediaOriginalHeight;

        var imagesContainerWidth = componentElement.parentNode.offsetWidth;
        var imagesContainerHeight = componentElement.parentNode.offsetHeight;

        var logic = imageInfo.getImageResizeType(window.mobileLogics.isPhoneActive());
        if (logic === 'contain') {
            imageWidth = imagesContainerWidth;
            imageHeight = imageWidth / aspectRatio;

            if (imageHeight > imagesContainerHeight) {
                imageHeight = imagesContainerHeight;
                imageWidth = imageHeight * aspectRatio;
            }
        } else {
            if (imageWidth > imagesContainerWidth) {
                imageWidth = imagesContainerWidth;
                imageHeight = imageWidth / aspectRatio;
            }

            if (imageHeight > imagesContainerHeight) {
                imageHeight = imagesContainerHeight;
                imageWidth = imageHeight * aspectRatio;
            }
        }

        var imageLeft = (imagesContainerWidth - imageWidth) / 2;
        var imageTop = (imagesContainerHeight - imageHeight) / 2;

        mediaElement.style.width = imageWidth + 'px';
        mediaElement.style.height = imageHeight + 'px';
        componentElement.style.width = imageWidth + 'px';
        componentElement.style.height = imageHeight + 'px';
        componentElement.style.left = imageLeft + 'px';
        componentElement.style.top = imageTop + 'px';
    };
    this.getImageWidth = function() {
        return mediaOriginalWidth;
    };
    this.getImageHeight = function() {
        return mediaOriginalHeight;
    };
    init();
};
SlideOverlayMixin.call(FullScreenGalleryImageComponent.prototype);

window.FullScreenGalleryCloseComponent = function(galleryComponent) {
    var self = this;
    this.componentElement = null;

    var init = function() {
        self.componentElement = document.createElement('div');
        self.componentElement.className = 'fullscreen_gallery_close';
        eventsManager.addHandler(self.componentElement, 'click', clickHandler);
    };
    var clickHandler = function(event) {
        galleryComponent.hideComponent();
    };
    init();
};
window.FullScreenGalleryNextComponent = function(galleryObject) {
    var self = this;
    this.componentElement = null;
    var buttonNextElem;

    var init = function() {
        self.componentElement = document.createElement('div');
        self.componentElement.className = 'fullscreen_gallery_next';
        buttonNextElem = document.createElement('div');
        buttonNextElem.className = 'fullscreen_gallery_button_next';
        self.componentElement.appendChild(buttonNextElem);
        eventsManager.addHandler(self.componentElement, 'click', clickHandler);
    };
    var clickHandler = function(event) {
        galleryObject.displayNextImage();
    };
    this.adjustHeight = function(heightElement) {
        self.componentElement.style.height = heightElement.offsetHeight + 'px';
    };

    init();
};
window.FullScreenGalleryPrevComponent = function(galleryObject) {
    var self = this;
    this.componentElement = null;
    var buttonPrevElem;

    var init = function() {
        self.componentElement = document.createElement('div');
        self.componentElement.className = 'fullscreen_gallery_prev';
        buttonPrevElem = document.createElement('div');
        buttonPrevElem.className = 'fullscreen_gallery_button_prev';
        self.componentElement.appendChild(buttonPrevElem);
        eventsManager.addHandler(self.componentElement, 'click', clickHandler);
    };
    var clickHandler = function(event) {
        galleryObject.displayPreviousImage();
    };
    this.adjustHeight = function(heightElement) {
        self.componentElement.style.height = heightElement.offsetHeight + 'px';
    };
    init();
};