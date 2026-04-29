window.galleriesLogics = new function() {
    var galleriesIndex;
    var initLogics = function() {
        galleriesIndex = {};
        if (typeof galleriesInfo !== 'undefined') {
            for (var id in galleriesInfo) {
                var galleryItem = new GalleryItem(galleriesInfo[id]);
                galleriesIndex[galleryItem.getId()] = galleryItem;
            }
        }
    };
    var initComponents = function() {
        var elements, i, component;
        for (var id in galleriesIndex) {
            var galleryInfo = galleriesIndex[id];
            if (galleryInfo) {
                elements = _('.galleryid_' + id);
                for (i = 0; i < elements.length; i++) {
                    component = false;
                    if (elements[i].className.indexOf('gallery_static') !== -1) {
                        if (typeof StaticGalleryComponent !== 'undefined') {
                            component = new StaticGalleryComponent(elements[i], galleryInfo);
                        }
                    } else if (elements[i].className.indexOf('gallery_slide') !== -1) {
                        component = new GalleryComponent(elements[i], galleryInfo, 'slide');
                    } else if (elements[i].className.indexOf('gallery_scroll') !== -1) {
                        component = new GalleryComponent(elements[i], galleryInfo, 'scroll');
                    } else if (elements[i].className.indexOf('gallery_carousel') !== -1) {
                        component = new GalleryComponent(elements[i], galleryInfo, 'carousel');
                    }
                    if (component) {
                        controller.addListener('startApplication', component.init);
                    }
                }
            }
        }
    };
    this.getGalleriesIndex = function() {
        return galleriesIndex;
    };
    this.getGalleryInfo = function(id) {
        if (typeof galleriesIndex[id] !== 'undefined') {
            return galleriesIndex[id];
        }
        return false;
    };
    controller.addListener('initDom', initComponents);
    controller.addListener('initLogics', initLogics);
};
window.GalleryItem = function(info) {
    var self = this;

    var id;
    var galleryResizeType;
    var galleryWidth;
    var galleryHeight;
    var thumbnailsSelectorEnabled;
    var thumbnailsSelectorHeight;
    var fullScreenGalleryEnabled;
    var imageResizeType;
    var changeDelay = 6000;
    var showDelay = 0;
    var imagesButtonsEnabled = false;
    var playbackButtonEnabled = false;
    var descriptionType = 'none';
    var descriptionEffect = 'none';
    var imagesPrevNextButtonsEnabled = false;
    var imagesPrevNextButtonsSeparated = false;
    var fullScreenButtonEnabled = false;
    var imageAspectRatio;
    var videoAutoStart = false;
    var mobileGalleryHeight = false;
    var mobileGalleryResizeType = false;
    var mobileImageResizeType = false;
    var mobileThumbnailsSelectorEnabled;
    var mobileEnablePrevNextImagesButtons;
    var mobileImagesButtonsEnabled;
    var imagesIndex;
    var imagesList;
    var currentImage;
    var slideShowActive = false;
    var interval;

    var init = function() {
        importData(info);
        if (imagesList.length > 0) {
            currentImage = imagesList[0];
        }
    };
    var importData = function(info) {
        imagesIndex = {};
        imagesList = [];
        id = parseInt(info.id, 10);

        if (typeof info.galleryResizeType !== 'undefined') {
            galleryResizeType = info.galleryResizeType;
        } else {
            galleryResizeType = 'viewport';
        }

        if (typeof info.galleryWidth !== 'undefined') {
            galleryWidth = info.galleryWidth;
        } else {
            galleryWidth = null;
        }
        if (typeof info.height !== 'undefined') {
            galleryHeight = info.height;
        } else if (typeof info.galleryHeight !== 'undefined') {
            galleryHeight = info.galleryHeight;
        } else {
            galleryHeight = null;
        }
        if (typeof info.displaySelector !== 'undefined') {
            thumbnailsSelectorEnabled = info.displaySelector;
        } else if (typeof info.thumbnailsSelectorEnabled !== 'undefined') {
            thumbnailsSelectorEnabled = info.thumbnailsSelectorEnabled;
        } else {
            thumbnailsSelectorEnabled = false;
        }

        if (typeof info.thumbnailsSelectorHeight !== 'undefined') {
            thumbnailsSelectorHeight = info.thumbnailsSelectorHeight;
        } else {
            thumbnailsSelectorHeight = false;
        }
        showDelay = info.showDelay || 0;

        if (typeof info.fullScreenGallery !== 'undefined') {
            fullScreenGalleryEnabled = info.fullScreenGallery;
        } else if (typeof info.fullScreenGalleryEnabled !== 'undefined') {
            fullScreenGalleryEnabled = info.fullScreenGalleryEnabled;
        } else {
            fullScreenGalleryEnabled = true;
        }

        if (typeof info.changeDelay !== 'undefined') {
            changeDelay = parseInt(info.changeDelay, 10);
        } else {
            changeDelay = 6000;
        }

        if (typeof info.videoAutoStart !== 'undefined') {
            videoAutoStart = !!info.videoAutoStart;
        }

        if (typeof info.imageAspectRatio !== 'undefined') {
            imageAspectRatio = parseFloat(info.imageAspectRatio);
        }

        if (typeof info.imageResizeLogics !== 'undefined') {
            imageResizeType = info.imageResizeLogics;
        } else if (typeof info.imageResizeType !== 'undefined') {
            imageResizeType = info.imageResizeType;
        } else {
            imageResizeType = 'resize';
        }

        if (typeof info.descriptionType !== 'undefined') {
            descriptionType = info.descriptionType;
        }
        if (typeof info.descriptionEffect !== 'undefined') {
            descriptionEffect = info.descriptionEffect;
        }

        if (typeof info.mobileGalleryHeight !== 'undefined') {
            mobileGalleryHeight = info.mobileGalleryHeight;
        }
        if (typeof info.mobileGalleryResizeType !== 'undefined') {
            mobileGalleryResizeType = info.mobileGalleryResizeType;
        }
        if (typeof info.mobileImageResizeType !== 'undefined') {
            mobileImageResizeType = info.mobileImageResizeType;
        }
        if (typeof info.mobileThumbnailsSelectorEnabled !== 'undefined') {
            mobileThumbnailsSelectorEnabled = info.mobileThumbnailsSelectorEnabled;
        }
        if (typeof info.mobileEnablePrevNextImagesButtons !== 'undefined') {
            mobileEnablePrevNextImagesButtons = info.mobileEnablePrevNextImagesButtons;
        }
        if (typeof info.mobileImagesButtonsEnabled !== 'undefined') {
            mobileImagesButtonsEnabled = info.mobileImagesButtonsEnabled;
        }

        if (info.images.length > 1) {
            if (info.enableImagesButtons || info.imagesButtonsEnabled) {
                imagesButtonsEnabled = true;
            }
            if (info.enablePrevNextImagesButtons || info.imagesPrevNextButtonsEnabled) {
                imagesPrevNextButtonsEnabled = true;
            }
            if (info.imagesPrevNextButtonsSeparated) {
                imagesPrevNextButtonsSeparated = true;
            }
            if (info.enablePlaybackButton || info.playbackButtonEnabled) {
                playbackButtonEnabled = true;
            }
        }
        if (info.fullScreenButtonEnabled) {
            fullScreenButtonEnabled = true;
        }

        for (var i = 0; i < info.images.length; i++) {
            var galleryImage = new GalleryImage(info.images[i], self);
            imagesIndex[galleryImage.getId()] = galleryImage;
            imagesList.push(galleryImage);
        }
    };
    this.getDescriptionType = function() {
        return descriptionType;
    };
    this.getDescriptionEffect = function() {
        return descriptionEffect;
    };
    this.getChangeDelay = function() {
        return changeDelay;
    };
    this.getId = function() {
        return id;
    };
    this.isFullScreenGalleryEnabled = function() {
        return fullScreenGalleryEnabled;
    };
    this.getImageResizeType = function(mobile) {
        if (mobile && mobileImageResizeType) {
            return mobileImageResizeType;
        }
        return imageResizeType;
    };
    this.getGalleryResizeType = function(mobile) {
        if (mobile && mobileGalleryResizeType) {
            return mobileGalleryResizeType;
        }
        return galleryResizeType;
    };
    this.getGalleryWidth = function() {
        return galleryWidth;
    };
    this.getShowDelay = function() {
        return showDelay;
    };
    this.getGalleryHeight = function(mobile) {
        if (mobile && mobileGalleryHeight) {
            return mobileGalleryHeight;
        }
        return galleryHeight;
    };
    this.getImagesList = function() {
        return imagesList;
    };
    this.getImageAspectRatio = function() {
        return imageAspectRatio;
    };
    this.getVideoAutoStart = function() {
        return videoAutoStart;
    };
    this.displayImageByNumber = function(number) {
        if (typeof imagesList[number] !== 'undefined') {
            return self.displayImage(imagesList[number].getId());
        }
        return false;
    };
    this.displayImage = function(imageId) {
        if (typeof imagesIndex[imageId] !== 'undefined') {
            currentImage = imagesIndex[imageId];
        }
        controller.fireEvent('galleryImageDisplay', currentImage);
    };
    this.getCurrentImage = function() {
        return currentImage;
    };
    this.getCurrentImageNumber = function() {
        var currentImageId = currentImage.getId();
        return self.getImageNumber(currentImageId);
    };
    this.getImageNumber = function(imageId) {
        for (var i = 0; i < imagesList.length; i++) {
            if (imagesList[i].getId() == imageId) {
                return i;
            }
        }
        return false;
    };
    this.isThumbnailsSelectorEnabled = function(mobile) {
        var result = false;
        if (mobile && mobileThumbnailsSelectorEnabled !== null) {
            result = mobileThumbnailsSelectorEnabled;
        } else {
            result = thumbnailsSelectorEnabled;
        }
        if (imagesList.length <= 1) {
            result = false;
        }
        return result;
    };
    this.getThumbnailsSelectorHeight = function() {
        return thumbnailsSelectorHeight;
    };
    this.displayNextImage = function() {
        self.displayImage(self.getNextImage(true));
    };
    this.displayPreviousImage = function() {
        self.displayImage(self.getPrevImage(true));
    };
    this.getNextImage = function(infiniteLoop) {
        var currentImageId = currentImage.getId();
        for (var i = 0; i < imagesList.length; i++) {
            if (imagesList[i].getId() == currentImageId) {
                if (typeof imagesList[i + 1] !== 'undefined') {
                    return imagesList[i + 1].getId();
                } else if (infiniteLoop) {
                    return imagesList[0].getId();
                }
            }
        }
        return false;
    };
    this.getPrevImage = function(infiniteLoop) {
        var currentImageId = currentImage.getId();
        for (var i = 0; i < imagesList.length; i++) {
            if (imagesList[i].getId() == currentImageId) {
                if (typeof imagesList[i - 1] !== 'undefined') {
                    return imagesList[i - 1].getId();
                } else if (infiniteLoop) {
                    return imagesList[imagesList.length - 1].getId();
                }
            }
        }
        return false;
    };
    this.areImagesButtonsEnabled = function(mobile) {
        if (mobile && mobileImagesButtonsEnabled !== null) {
            return mobileImagesButtonsEnabled;
        }
        return imagesButtonsEnabled;
    };
    this.areImagesPrevNextButtonsEnabled = function(mobile) {
        if (mobile && mobileEnablePrevNextImagesButtons !== null) {
            return mobileEnablePrevNextImagesButtons;
        }
        return imagesPrevNextButtonsEnabled;
    };
    this.areImagesPrevNextButtonsSeparated = function() {
        return imagesPrevNextButtonsSeparated;
    };
    this.isFullScreenButtonEnabled = function() {
        return fullScreenButtonEnabled;
    };
    this.isPlaybackButtonEnabled = function() {
        return playbackButtonEnabled;
    };
    this.stopSlideShow = function() {
        slideShowActive = false;
        window.clearInterval(interval);
        controller.fireEvent('gallerySlideShowUpdated', id);
    };
    this.startSlideShow = function() {
        if (imagesList.length > 1) {
            slideShowActive = true;
            window.clearInterval(interval);
            interval = window.setInterval(self.displayNextImage, changeDelay);
        }

        controller.fireEvent('gallerySlideShowUpdated', id);
    };
    this.isSlideShowActive = function() {
        return slideShowActive;
    };

    init();
};
/**
 *
 * @param info
 * @param {GalleryItem} galleryObject
 * @constructor
 */
window.GalleryImage = function(info, galleryObject) {
    var self = this;
    var id;
    var bigImageSrcSet;
    var fullImageUrl;
    var bigImageUrl;
    var thumbnailImageUrl;
    var mobileFullImageUrl;
    var mobileBigImageUrl;
    var mobileThumbnailImageUrl;
    var fileUrl;
    var filename;
    var title;
    var description;
    var alt;
    var link;
    var externalLink;
    var isVideo = false;

    var init = function() {
        importData(info);
    };
    var importData = function(info) {
        id = parseInt(info.id, 10);
        fullImageUrl = info.fullImageUrl;
        bigImageUrl = info.bigImageUrl;
        thumbnailImageUrl = info.thumbnailImageUrl;
        bigImageSrcSet = info.bigImageSrcSet;

        if (typeof info.mobileFullImageUrl !== 'undefined') {
            mobileFullImageUrl = info.mobileFullImageUrl;
        }
        if (typeof info.mobileBigImageUrl !== 'undefined') {
            mobileBigImageUrl = info.mobileBigImageUrl;
        }
        if (typeof info.mobileThumbnailImageUrl !== 'undefined') {
            mobileThumbnailImageUrl = info.mobileThumbnailImageUrl;
        }
        fileUrl = info.fileUrl;
        filename = info.filename;
        title = info.title;
        description = info.description;
        alt = info.alt;
        link = info.link;
        externalLink = info.externalLink;
        if (filename) {
            var parts = filename.split('.');
            var extension = parts[parts.length - 1].toLowerCase();
            if (extension == 'mp4') {
                isVideo = true;
            }
        }

    };
    this.getId = function() {
        return id;
    };
    this.getTitle = function() {
        return title;
    };
    this.getDescription = function() {
        return description;
    };
    this.getFullImageUrl = function(mobile) {
        if (mobile) {
            return mobileFullImageUrl;
        }
        return fullImageUrl;
    };
    this.getBigImageUrl = function(mobile) {
        if (mobile) {
            return mobileFullImageUrl;
        }
        return bigImageUrl;
    };
    this.getBigImageSrcSet = function(mobile) {
        // if (mobile) {
        //     return mobileBigImageSrcSet;
        // }
        return bigImageSrcSet;
    };
    this.getThumbnailImageUrl = function(mobile) {
        // if (mobile) {
        //     return mobileFullImageUrl;
        // }
        return thumbnailImageUrl;
    };
    this.getFileUrl = function() {
        return fileUrl;
    };
    this.getFilename = function() {
        return filename;
    };
    this.getExternalLink = function() {
        return externalLink;
    };
    this.getLink = function() {
        return link;
    };
    this.isVideo = function() {
        return isVideo;
    };
    this.openExternalLink = function() {
        if (self.isNewWindowUsed()) {
            window.open(self.getExternalLink(), '_blank');
        } else {
            document.location.href = self.getExternalLink();
        }
    };
    this.isNewWindowUsed = function() {
        var tempLink = document.createElement('a');
        tempLink.href = externalLink;
        return (window.location.hostname != tempLink.hostname) && (tempLink.hostname !== '');
    };
    this.getImageNumber = function() {
        return galleryObject.getImageNumber(id);
    };
    this.display = function() {
        galleryObject.displayImage(id);
    };
    this.getImageResizeType = function(mobile) {
        return galleryObject.getImageResizeType(mobile);
    };
    this.getGallery = function() {
        return galleryObject;
    };
    init();
};