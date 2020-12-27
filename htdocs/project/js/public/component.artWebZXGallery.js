window.artWebZXGallery = function(galleryElement) {
    this.init = function() {
        this.galleryElement = galleryElement;
        if (window.galleryPictures) {
            this.galleryPictures = window.galleryPictures;
            this.createDomStructure();
            var imagesElements = _('.zxgallery_item', self.galleryElement);
            for (var i = 0; i < imagesElements.length; i++) {
                self.contentImagesList.push(new artWebAuthorImage(imagesElements[i], this));
            }
            eventsManager.addHandler(window, 'keydown', self.keyDownHandler);
            eventsManager.addHandler(window, 'resize', self.reposition);
        }
    };
    this.createDomStructure = function() {
        this.componentElement = document.createElement('div');
        this.componentElement.style.display = 'none';
        this.componentElement.style.position = 'fixed';
        this.componentElement.style.top = '0';
        this.componentElement.style.left = '0';
        this.componentElement.style.width = '990px';
        this.componentElement.style.height = '100%';
        this.componentElement.style.zIndex = '100';
        eventsManager.addHandler(this.componentElement, 'click', this.hideComponent);

        this.bigImageContainer = document.createElement('div');
        this.bigImageContainer.style.position = 'absolute';
        this.bigImageContainer.style.top = '0';
        this.bigImageContainer.style.left = '0';
        this.bigImageContainer.style.width = '640px';
        this.bigImageContainer.style.height = '480px';
        this.bigImageContainer.style.maxWidth = '100%';

        this.componentElement.appendChild(this.bigImageContainer);

        this.titleBlock = document.createElement('div');
        this.titleBlock.className = 'fullscreen_title';
        this.titleBlock.style.position = 'absolute';
        this.titleBlock.style.top = '500px';
        this.titleBlock.style.left = '25px';

        this.componentElement.appendChild(this.titleBlock);

        this.thumbnailsContainer = document.createElement('div');
        this.thumbnailsContainer.style.position = 'absolute';
        this.thumbnailsContainer.style.top = '0';
        this.thumbnailsContainer.style.bottom = '0';
        this.thumbnailsContainer.style.right = '0';
        this.thumbnailsContainer.style.width = '335px';
        this.thumbnailsContainer.style.overflow = 'auto';

        this.componentElement.appendChild(this.thumbnailsContainer);

        for (var i in this.galleryPictures) {
            var imageInfo = window.imageInfoIndex[this.galleryPictures[i]];
            var thumbnail = new artWebAuthorThumbnail(imageInfo, self);
            this.thumbnailsContainer.appendChild(thumbnail.componentElement);

            this.thumbnailsList.push(thumbnail);
            this.thumbnailsIndex[thumbnail.id] = thumbnail;
        }

        document.body.appendChild(this.componentElement);
    };
    this.markActiveThumbnail = function() {
        if (this.previousImageId && this.currentImageId != this.previousImageId) {
            if (this.thumbnailsIndex[this.previousImageId]) {
                this.thumbnailsIndex[this.previousImageId].markInactive();
            }
        }
        if (this.currentImageId) {
            if (this.thumbnailsIndex[this.currentImageId]) {
                this.thumbnailsIndex[this.currentImageId].markActive();
            }
        }
    };
    this.showImage = function(imageId) {
        if (this.currentImageId) {
            this.previousImageId = this.currentImageId;
            if (this.largeImagesIndex[this.previousImageId]) {
                this.largeImagesIndex[this.previousImageId].componentElement.style.zIndex = '5';
            }
        }
        this.currentImageId = imageId;

        this.markActiveThumbnail();

        if (!this.displayed) {
            this.hidePreviousImage();
            window.DarkLayerComponent.showLayer(this.hideComponent, this.displayComponent, true);
        } else {
            if (this.previousImageId != this.currentImageId) {
                this.checkImageObject();
            }
        }
    };
    this.displayComponent = function() {
        self.componentElement.style.display = 'block';
        self.reposition();
        self.displayed = true;
        self.checkImageObject();
    };
    this.reposition = function() {
        var viewPortLeft, viewPortWidth;
        if (window.pageYOffset) {
            viewPortLeft = window.pageXOffset;
        } else {
            viewPortLeft = document.documentElement.scrollLeft;
        }

        if (window.innerHeight) {
            viewPortWidth = window.innerWidth;
        } else {
            viewPortWidth = document.documentElement.offsetWidth;
        }
        if (viewPortWidth < 990) {
            self.bigImageContainer.style.position = 'relative';
            self.bigImageContainer.style.margin = '0 auto';
            self.thumbnailsContainer.style.display = 'none';
            self.componentElement.style.width = 'auto';
            self.componentElement.style.left = 0;
            self.componentElement.style.right = 0;
        } else {
            self.bigImageContainer.style.position = 'absolute';
            self.bigImageContainer.style.margin = '0';
            self.thumbnailsContainer.style.display = '';
            self.componentElement.style.right = 'auto';
            self.componentElement.style.width = '990px';
            self.componentElement.style.left = viewPortLeft + (viewPortWidth - self.componentElement.offsetWidth) / 2 + 'px';
        }
    };
    this.checkImageObject = function() {
        this.hideContentImages();
        var currentImageId = self.currentImageId;
        if (!self.largeImagesIndex[currentImageId]) {
            if (window.imageInfoIndex[currentImageId]) {
                var imageInfo = window.imageInfoIndex[currentImageId];
                var largeImageObject = new artWebAuthorLargeImage(imageInfo, self);
                self.largeImagesIndex[currentImageId] = largeImageObject;
                self.bigImageContainer.appendChild(largeImageObject.componentElement);
                self.titleBlock.textContent = largeImageObject.title;
            }
        }
        if (self.largeImagesIndex[currentImageId]) {
            self.largeImagesIndex[currentImageId].checkPreload();
        }
    };
    this.displayContentImages = function() {
        for (var i = 0; i < self.contentImagesList.length; i++) {
            self.contentImagesList[i].displayComponent();
        }
    };
    this.hideContentImages = function() {
        for (var i = 0; i < self.contentImagesList.length; i++) {
            self.contentImagesList[i].hideComponent();
        }
    };
    this.hidePreviousImage = function() {
        for (var index in self.largeImagesIndex) {
            if (index != self.currentImageId) {
                self.largeImagesIndex[index].hideComponent();
            }
        }
    };
    this.hideComponent = function() {
        self.displayContentImages();
        self.componentElement.style.display = 'none';
        self.displayed = false;
        window.DarkLayerComponent.hideLayer();
    };
    this.keyDownHandler = function(event) {
        if (self.displayed) {
            if (event.keyCode == '27') {
                eventsManager.preventDefaultAction(event);
                self.hideComponent();
            }
            if (event.keyCode == '32') {
                eventsManager.preventDefaultAction(event);
                self.showNextImage();
            }
        }
    };
    this.showNextImage = function() {
        var nextImage = 0;

        if (self.currentImageId) {
            for (var i = 0; i < self.thumbnailsList.length; i++) {
                if (self.thumbnailsList[i].id == self.currentImageId) {
                    nextImage = i + 1;
                    break;
                }
            }
        }

        if (nextImage >= self.thumbnailsList.length) {
            nextImage = 0;
        }
        self.showImage(self.thumbnailsList[nextImage].id);
    };
    var self = this;

    this.displayed = false;
    this.parentId = false;
    this.galleryPictures = false;

    this.currentImageId = false;
    this.previousImageId = false;

    this.galleryElement = false;
    this.componentElement = false;
    this.bigImageContainer = false;
    this.thumbnailsContainer = false;

    this.largeImagesIndex = {};
    this.contentImagesList = [];
    this.thumbnailsList = [];
    this.thumbnailsIndex = {};

    this.init();
};
window.artWebAuthorImage = function(componentElement, parentComponent) {
    this.init = function() {
        this.componentElement = componentElement;
        this.parentComponent = parentComponent;

        if (zxPicturesLogics.getReplaceFlickering()) {
            if (this.componentElement.className.search('flicker_image') >= 0) {
                var flickerImage = new FlickerImageComponent(this.componentElement);
                this.componentElement = flickerImage.getCanvasElement();
            }
        }

        this.id = this.componentElement.id.split('_')[1];

        self.componentElement.style.cursor = 'pointer';

        eventsManager.addHandler(this.componentElement, 'click', this.clickHandler);
        eventsManager.addHandler(this.componentElement, 'mouseenter', this.mouseEnterHandler);
        eventsManager.addHandler(this.componentElement, 'mouseleave', this.mouseLeaveHandler);
    };
    this.clickHandler = function() {
        self.parentComponent.showImage(self.id);
    };
    this.displayComponent = function() {
        self.componentElement.style.visibility = 'visible';
    };
    this.hideComponent = function() {
        self.componentElement.style.visibility = 'hidden';
    };
    this.mouseEnterHandler = function() {
        self.componentElement.style.border = '1px solid #809ECF';
    };
    this.mouseLeaveHandler = function() {
        self.componentElement.style.border = '1px solid #000000';
    };
    var self = this;

    this.id = false;

    this.componentElement = false;
    this.parentComponent = false;
    this.init();
};
window.artWebAuthorThumbnail = function(imageInfo, parentComponent) {
    this.init = function() {
        this.componentElement = document.createElement('div');

        this.active = false;

        this.componentElement.style.width = '64px';
        this.componentElement.style.padding = '4px';
        this.componentElement.style.margin = '2px';
        this.componentElement.style.border = '1px solid #000000';
        this.componentElement.style.display = 'inline-block';

        this.imageElement = document.createElement('img');
        this.imageElement.src = imageInfo.smallImage;
        this.imageElement.style.display = 'block';
        this.imageElement.style.maxWidth = '100%';

        this.componentElement.appendChild(this.imageElement);
        this.parentComponent = parentComponent;

        this.id = imageInfo.id;

        eventsManager.addHandler(this.componentElement, 'click', this.clickHandler);
        eventsManager.addHandler(this.componentElement, 'mouseenter', this.mouseEnterHandler);
        eventsManager.addHandler(this.componentElement, 'mouseleave', this.mouseLeaveHandler);

        this.refreshStatus();
    };
    this.clickHandler = function(event) {
        eventsManager.cancelBubbling(event);
        self.parentComponent.showImage(self.id);
    };
    this.mouseEnterHandler = function() {
        self.hovered = true;
        self.refreshStatus();
    };
    this.mouseLeaveHandler = function() {
        self.hovered = false;
        self.refreshStatus();
    };
    this.markActive = function() {
        this.active = true;
        this.refreshStatus();
    };
    this.markInactive = function() {
        this.active = false;
        this.refreshStatus();
    };
    this.refreshStatus = function() {
        if (self.active) {
            this.componentElement.style.cursor = 'auto';
            self.componentElement.style.border = '1px solid #809ECF';
        } else {
            this.componentElement.style.cursor = 'pointer';
            if (self.hovered) {
                self.componentElement.style.border = '1px solid #809ECF';
            } else {
                self.componentElement.style.border = '1px solid #000000';
            }
        }
    };
    var self = this;

    this.id = false;
    this.active = false;
    this.hovered = false;
    this.componentElement = false;
    this.parentComponent = false;
    this.init();
};
window.artWebAuthorLargeImage = function(imageInfo, parentComponent) {
    var preloaded = false;
    var canvasReplaced = false;
    this.init = function() {
        this.componentElement = document.createElement('img');
        this.componentElement.src = imageInfo.largeImage;
        this.componentElement.style.maxWidth = '100%';
        this.componentElement.style.position = 'absolute';
        this.componentElement.style.left = '0';
        this.componentElement.style.top = '0';
        this.componentElement.style.zIndex = '10';
        this.componentElement.style.cursor = 'pointer';
        self.componentElement.style.opacity = 0;
        this.componentElement.style.display = 'block';

        this.parentComponent = parentComponent;

        this.id = imageInfo.id;
        this.title = imageInfo.title;

        eventsManager.addHandler(this.componentElement, 'click', this.clickHandler);
    };
    this.checkPreload = function() {
        if (!preloaded) {
            if (zxPicturesLogics.getReplaceFlickering()) {
                if (imageInfo.flickering && !canvasReplaced) {
                    canvasReplaced = true;
                    var flickerImage = new FlickerImageComponent(self.componentElement);
                    self.componentElement = flickerImage.getCanvasElement();
                    self.componentElement.style.position = 'absolute';
                    self.componentElement.style.left = '0';
                    self.componentElement.style.top = '0';
                    self.componentElement.style.zIndex = '10';
                    self.componentElement.style.cursor = 'pointer';
                    self.componentElement.style.opacity = 0;
                    self.componentElement.style.display = 'block';
                }
            }

            if (self.componentElement.complete) {
                preloaded = true;
            }

            window.setTimeout(self.checkPreload, 100);
        } else {
            preloaded = true;
            self.displayComponent();
        }
    };
    this.displayComponent = function() {
        zxPicturesLogics.logView(self.id);

        self.componentElement.style.opacity = 0;
        self.componentElement.style.display = 'block';
        self.componentElement.style.zIndex = '15';

        TweenLite.to(self.componentElement, 0.2, {
            'css': {'opacity': 1},
            'onComplete': parentComponent.hidePreviousImage,
        });
    };
    this.hideComponent = function() {
        self.componentElement.style.display = 'none';
    };
    this.clickHandler = function(event) {
        eventsManager.cancelBubbling(event);
        self.parentComponent.showNextImage();
    };
    var self = this;

    this.id = false;

    this.componentElement = false;
    this.parentComponent = false;
    this.init();
};