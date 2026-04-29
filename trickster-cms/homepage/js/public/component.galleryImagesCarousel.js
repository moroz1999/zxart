window.GalleryImagesCarouselComponent = function(galleryInfo, parentComponent) {
    var componentElement;
    var self = this;
    var imagesList = [];
    var leftImagesList = [];
    var rightImagesList = [];
    var imagesIndex = [];
    var imageComponentElements = [];
    var leftImageComponentElements = [];
    var rightImageComponentElements = [];
    var currentImageNumber = 0;
    var width;
    var height;
    var fixedImagesWidth;
    var fixedImagesHeight;
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
        var i;
        if (imagesList = createImageComponents()) {
            for (i = 0; i < imagesList.length; i++) {
                componentElement.appendChild(imagesList[i].getComponentElement());
                imageComponentElements.push(imagesList[i].getComponentElement());
                imagesIndex[imagesList[i].getId()] = imagesList[i];
            }
        }
        if (leftImagesList = createImageComponents()) {
            for (i = 0; i < leftImagesList.length; i++) {
                componentElement.appendChild(leftImagesList[i].getComponentElement());
                leftImageComponentElements.push(leftImagesList[i].getComponentElement());
            }
        }
        if (rightImagesList = createImageComponents()) {
            for (i = 0; i < rightImagesList.length; i++) {
                componentElement.appendChild(rightImagesList[i].getComponentElement());
                rightImageComponentElements.push(rightImagesList[i].getComponentElement());
            }
        }
    };
    this.startApplication = function() {
        parentComponent.recalculateSizes();

        var aspectRatio = galleryInfo.getImageAspectRatio();
        if (!aspectRatio) {
            aspectRatio = componentElement.offsetWidth / componentElement.offsetHeight;
        }

        self.initCarouselGallery({
            'componentElement': componentElement,
            'pageElements': imageComponentElements,
            'leftPageElements': leftImageComponentElements,
            'rightPageElements': rightImageComponentElements,
            'rotateDelay': galleryInfo.getChangeDelay(),
            'rotateSpeed': 2.7,
            'autoStart': false,
            'imageAspectRatio': aspectRatio,
            'preloadCallBack': preloadCallBack,
            'touchStartCallBack': touchStartCallBack,
            'touchDisplayNextImageCallback': touchDisplayNextImageCallback,
            'touchDisplayPreviousImageCallback': touchDisplayPreviousImageCallback,
            'touchImageClick': touchImageClick,
        });
        galleryInfo.displayImageByNumber(0);
        galleryInfo.startSlideShow();
    };

    var touchStartCallBack = function() {
        galleryInfo.stopSlideShow();
    };

    var touchDisplayNextImageCallback = function() {
        galleryInfo.stopSlideShow();
        galleryInfo.displayNextImage();
    };

    var touchDisplayPreviousImageCallback = function() {
        galleryInfo.stopSlideShow();
        galleryInfo.displayPreviousImage();
    };

    var touchImageClick = function() {
        var currentImage = galleryInfo ? galleryInfo.getCurrentImage() : null;
        var link = currentImage ? currentImage.getLink() : '';
        if (link) {
            document.location.href = link;
        }
    };

    var createImageComponents = function() {
        var imageComponents = [];

        var imagesInfoList = galleryInfo.getImagesList();
        for (var i = 0; i < imagesInfoList.length; i++) {
            var imageComponent = new GalleryImageComponent(imagesInfoList[i], self, galleryInfo);
            imageComponents.push(imageComponent);
        }
        return imageComponents;
    };

    var galleryImageDisplayHandler = function(imageObject) {
        var imageId = imageObject.getId();
        if (imagesIndex[imageId]) {
            currentImageNumber = imageObject.getImageNumber();
            self.showPage(currentImageNumber);
        }
    };

    var preloadCallBack = function(number, callback) {
        if (typeof imagesList[number] !== 'undefined') {
            leftImagesList[number].checkPreloadImage(callback);
            imagesList[number].checkPreloadImage(callback);
            rightImagesList[number].checkPreloadImage(callback);
        }
    };

    this.setSizes = function(newWidth, newHeight) {
        width = newWidth;
        height = newHeight;
        componentElement.style.width = width + 'px';
        componentElement.style.height = height + 'px';
        if (galleryInfo.getImageAspectRatio()) {
            width = height * galleryInfo.getImageAspectRatio();
        }

        if (fixedImagesWidth) {
            width = fixedImagesWidth;
        }
        if (fixedImagesHeight) {
            height = fixedImagesHeight;
        }

        var i;
        for (i = leftImagesList.length; i--;) {
            leftImagesList[i].resize(width, height);
        }
        for (i = imagesList.length; i--;) {
            imagesList[i].resize(width, height);
        }
        for (i = rightImagesList.length; i--;) {
            rightImagesList[i].resize(width, height);
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

    this.setFixedImageSizes = function(newWidth, newHeight) {
        fixedImagesWidth = newWidth;
        fixedImagesHeight = newHeight;
    };

    init();
};
CarouselPagesMixin.call(GalleryImagesCarouselComponent.prototype);