window.GalleryImagesScrollComponent = function(galleryInfo, parentComponent) {
    var componentElement;
    var self = this;
    var imagesList = [];
    var imagesIndex = [];
    var imageComponentElements = [];
    var currentImageNumber = 0;
    var width;

    var height;
    var fullScreenGallery;

    var startX, startY, startScrollX, scrolling, touchEndTimer, lastX, startTime, velocity, direction;
    var eventsSet;
    var userHasInteracted;
    var scrollTween;
    var completeMovementTimeout;
    var acceleration = 100;

    var init = function() {
        if (galleryInfo.getImagesList()) {
            initDomStructure();

            if (galleryInfo.isFullScreenGalleryEnabled()) {
                fullScreenGallery = new FullScreenGalleryComponent(galleryInfo);
            }
            controller.addListener('galleryImageDisplay', galleryImageDisplayHandler);

            eventsSet = eventsManager.detectTouchEventsSet();

            eventsManager.addHandler(componentElement, eventsManager.getPointerStartEventName(), touchStart);

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
        self.scrollPagesInit({
            'componentElement': componentElement,
            'pageElements': imageComponentElements,
            'interval': galleryInfo.getChangeDelay(),
            'changeDuration': 1,
            'effectDuration': 1.5,
            'autoStart': false,
            'preloadCallBack': preloadCallBack,
        });
        galleryInfo.displayImageByNumber(0);
        galleryInfo.startSlideShow();
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

    var touchStart = function(event) {
        if (scrollTween) {
            scrollTween.kill();
        }
        window.clearTimeout(completeMovementTimeout);

        direction = false;
        if (!userHasInteracted) {
            userHasInteracted = true;
            galleryInfo.stopSlideShow();
        }
        eventsManager.preventDefaultAction(event);
        if (eventsSet == 'touch') {
            startX = event.touches[0].pageX;
            startY = event.touches[0].pageY;
        } else if (eventsSet == 'MSPointer') {
            startX = event.pageX;
            startY = event.pageY;
        } else {
            startX = event.pageX;
            startY = event.pageY;
        }
        lastX = startX;
        startTime = Number(new Date());
        startScrollX = componentElement.scrollLeft;
        eventsManager.addHandler(document, eventsManager.getPointerMoveEventName(), touchMove);
        eventsManager.addHandler(document, eventsManager.getPointerEndEventName(), touchEnd);
        eventsManager.addHandler(document, eventsManager.getPointerCancelEventName(), touchEnd);
    };

    var touchMove = function(event) {
        eventsManager.preventDefaultAction(event);
        var currentX = startX;
        var currentY = startY;
        if (eventsSet == 'touch') {
            currentX = event.touches[0].pageX;
            currentY = event.touches[0].pageY;
        } else if (eventsSet == 'MSPointer') {
            currentX = event.pageX;
            currentY = event.pageY;
        } else {
            currentX = event.pageX;
            currentY = event.pageY;
        }

        if (currentX - startX > 0) {
            direction = 'right';
        } else {
            direction = 'left';
        }

        velocity = Math.abs((currentX - startX)) / ((Number(new Date()) - startTime) / 1000);

        if (!scrolling) {
            if ((currentX != startX) && (Math.abs((currentX - startX) / startX) > 0.2)) {
                scrolling = true;
            }
        }
        if (scrolling) {
            window.clearTimeout(touchEndTimer);
            touchEndTimer = window.setTimeout(touchEnd, 2000);
            componentElement.scrollLeft = startScrollX - currentX + startX;
        } else {
            window.scrollBy(0, startY - currentY);
        }
        lastX = currentX;
    };

    var touchEnd = function() {
        window.clearTimeout(touchEndTimer);
        eventsManager.removeHandler(document, eventsManager.getPointerMoveEventName(), touchMove);
        eventsManager.removeHandler(document, eventsManager.getPointerEndEventName(), touchEnd);
        eventsManager.removeHandler(document, eventsManager.getPointerCancelEventName(), touchEnd);
        if (scrolling) {
            scrolling = false;
            velocity = velocity / 10;
            completeMovement();
        } else {
            var imageInfo = imagesList[currentImageNumber].getImageInfo();
            if (imageInfo.getExternalLink()) {
                imageInfo.openExternalLink();
            }
        }
    };

    var completeMovement = function() {
        var scrollIndex = checkScroll();
        velocity -= acceleration;

        if (velocity > 0 && scrollIndex == currentImageNumber) {
            if (direction == 'left') {
                componentElement.scrollLeft += velocity;
            } else {
                componentElement.scrollLeft -= velocity;
            }
            completeMovementTimeout = setTimeout(completeMovement, 100);
        } else {
            scroll(scrollIndex);
        }
    };
    var checkScroll = function() {
        var windowScrollLeft = componentElement.scrollLeft;
        var windowOffsetWidth = componentElement.offsetWidth;
        var focusTicketIndex;
        var compIntersection = 0;
        for (var i = 0, l = imagesList.length; i !== l; i++) {
            var imageElement = imagesList[i].getComponentElement();

            var intersection = Math.min(imageElement.offsetLeft + imageElement.offsetWidth, windowScrollLeft + windowOffsetWidth) - Math.max(imageElement.offsetLeft, windowScrollLeft);

            if (intersection > compIntersection) {
                focusTicketIndex = i;
                compIntersection = intersection;
            }
        }
        return focusTicketIndex;
    };
    var scroll = function(focusIndex) {
        var imageElement = imagesList[focusIndex].getComponentElement();
        var scrollLeft = imageElement.offsetLeft + (imageElement.offsetWidth - componentElement.offsetWidth) / 2;

        scrollTween = TweenLite.to(componentElement, 0.5, {'scrollLeft': scrollLeft});
        currentImageNumber = focusIndex;
    };

    init();
};
ScrollPagesMixin.call(GalleryImagesScrollComponent.prototype);