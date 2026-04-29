window.GalleryImagesSlideComponent = function(galleryInfo, parentComponent) {
    var componentElement;
    var self = this;
    var imagesList = [];
    var imagesIndex = [];
    var imageComponentElements = [];
    var currentImageNumber = 0;
    var width;

    var height;
    var fullScreenGallery;

    var init = function() {
        if (galleryInfo.getImagesList()) {
            initDomStructure();

            if (galleryInfo.isFullScreenGalleryEnabled()) {
                fullScreenGallery = new FullScreenGalleryComponent(galleryInfo);
            }
            controller.addListener('galleryImageDisplay', galleryImageDisplayHandler);
        }
    };
    this.destroy = function() {
        controller.removeListener('galleryImageDisplay', galleryImageDisplayHandler);
        for (var i = imagesList.length; i--;) {
            imagesList[i].destroy();
        }
    };
    var initDomStructure = function() {
        componentElement = document.createElement('div');
        componentElement.className = 'gallery_images';

        var imagesInfoList = galleryInfo.getImagesList();
        for (var i = 0; i < imagesInfoList.length; i++) {
            var imageItem = new GalleryImageComponent(imagesInfoList[i], self, galleryInfo);
            componentElement.appendChild(imageItem.getComponentElement());
            imageComponentElements.push(imageItem.getComponentElement());
            imagesList.push(imageItem);
            imagesIndex[imageItem.getId()] = imageItem;
        }
    };

    this.startApplication = function() {
        parentComponent.recalculateSizes();
        self.initSlides({
            'componentElement': componentElement,
            'slideElements': imageComponentElements,
            'interval': galleryInfo.getChangeDelay(),
            'changeDuration': 1,
            'heightCalculated': false,
            'autoStart': false,
            'preloadCallBack': preloadCallBack,
        });
        galleryInfo.displayImageByNumber(0);
        var showDelay = 0;
        if (typeof galleryInfo.getShowDelay === 'function') {
            showDelay = galleryInfo.getShowDelay();
        }
        if (showDelay > 0) {
            window.setTimeout(function() {
                galleryInfo.startSlideShow();
            }, showDelay);
        } else {
            galleryInfo.startSlideShow();
        }
    };

    var galleryImageDisplayHandler = function(imageObject) {
        var imageId = imageObject.getId();
        if (imagesIndex[imageId]) {
            currentImageNumber = imageObject.getImageNumber();
            self.showSlide(currentImageNumber);
        }
    };

    var preloadCallBack = function(number, callback) {
        if (typeof imagesList[number] !== 'undefined') {
            imagesList[number].checkPreloadImage(callback);
        }
    };

    this.setSizes = function(newWidth, newHeight) {
        width = newWidth;
        height = newHeight;
        componentElement.style.width = width + 'px';
        componentElement.style.height = height + 'px';

        for (var i = imagesList.length; i--;) {
            imagesList[i].resize(width, height);
        }
    };

    this.displayFullScreenGallery = function() {
        if (fullScreenGallery) {
            galleryInfo.stopSlideShow();
            fullScreenGallery.display();
        }
    };

    this.getComponentElement = function() {
        return componentElement;
    };

    this.hasFullScreenGallery = function() {
        return galleryInfo.isFullScreenGalleryEnabled();
    };

    this.videoAutoStart = function() {
        return galleryInfo.getVideoAutoStart();
    };

    init();
};
SlidesMixin.call(GalleryImagesSlideComponent.prototype);