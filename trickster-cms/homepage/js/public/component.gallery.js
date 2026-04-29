window.GalleryComponent = function(componentElement, galleryInfo, type) {
    var self = this;
    var selectorComponent;
    var imagesComponent;
    var descriptionComponent;
    var buttonsContainerElement;
    var prevButtonContainerElement;
    var nextButtonContainerElement;
    var playBackButton;
    var fullScreenButton;
    var buttonPrevious;
    var buttonNext;
    var preloadedStructureElement;
    var imageButtons = [];

    this.init = function() {
        if (imagesComponent) {
            imagesComponent.startApplication();
        }
    };
    var construct = function() {
        domHelper.addClass(componentElement, 'gallery_' + type);
        var imagesList = galleryInfo.getImagesList();
        if (imagesList) {
            makeDomStructure();
            mobileBreakpointCallback();
            self.recalculateSizes();
            window.addEventListener('resize', self.recalculateSizes);
            window.addEventListener('orientationchange', self.recalculateSizes);

            controller.addListener('mobileBreakpointChanged', mobileBreakpointCallback);
        }
    };
    var mobileBreakpointCallback = function() {
        createButtons();
        createThumbnailsSelector();
    };
    var makeDomStructure = function() {
        while (componentElement.firstChild) {
            var structureChild = componentElement.firstChild;
            if ((typeof structureChild.className != 'undefined') && (structureChild.className.indexOf('gallery_structure') >= 0)) {
                preloadedStructureElement = structureChild;
            }
            componentElement.removeChild(componentElement.firstChild);
        }
        if (preloadedStructureElement) {
            componentElement.appendChild(preloadedStructureElement);
        }
        if (type == 'scroll') {
            imagesComponent = new GalleryImagesScrollComponent(galleryInfo, self);
        } else if (type == 'slide') {
            imagesComponent = new GalleryImagesSlideComponent(galleryInfo, self);
        } else if (type == 'carousel') {
            imagesComponent = new GalleryImagesCarouselComponent(galleryInfo, self);
        }
        if (preloadedStructureElement) {
            var gallery_images_container = create('gallery_images_container');
            gallery_images_container.appendChild(imagesComponent.getComponentElement());
        } else {
            componentElement.appendChild(imagesComponent.getComponentElement());
        }
        createButtons();
        var imagesInfosList = galleryInfo.getImagesList();
        if (galleryInfo.getDescriptionType() === 'static') {
            descriptionComponent = new GalleryDescriptionComponent(galleryInfo);
            if (preloadedStructureElement) {
                var gallery_description_container = create('gallery_description_container');
                gallery_description_container.appendChild(descriptionComponent.getComponentElement());
            } else {
                componentElement.appendChild(descriptionComponent.getComponentElement());
            }
            descriptionComponent.setDescription(imagesInfosList[0]);
        }
        createThumbnailsSelector();
    };
    var createThumbnailsSelector = function() {
        if (galleryInfo.isThumbnailsSelectorEnabled(window.mobileLogics.isPhoneActive())) {
            if (!selectorComponent) {
                if (typeof GallerySelectorComponent !== 'undefined') {
                    selectorComponent = new GallerySelectorComponent(galleryInfo, imagesComponent);
                    componentElement.appendChild(selectorComponent.getComponentElement());
                }
            }
        } else if (selectorComponent) {
            var element = selectorComponent.getComponentElement();
            if (element) {
                componentElement.removeChild(element);
            }
            selectorComponent = null;
        }
    };
    var createButtons = function() {
        // add buttons
        var imagesInfosList = galleryInfo.getImagesList();
        var imageNumber = 0;

        var imagesPrevNextButtonsEnabled = galleryInfo.areImagesPrevNextButtonsEnabled(window.mobileLogics.isPhoneActive());
        var imagesPrevNextButtonsSeparated = galleryInfo.areImagesPrevNextButtonsSeparated();
        var imagesButtonsEnabled = galleryInfo.areImagesButtonsEnabled(window.mobileLogics.isPhoneActive());
        var playbackButtonEnabled = galleryInfo.isPlaybackButtonEnabled();
        var fullScreenButtonEnabled = galleryInfo.isFullScreenButtonEnabled();
        var button;
        if (playbackButtonEnabled || imagesButtonsEnabled || imagesPrevNextButtonsEnabled || fullScreenButtonEnabled) {
            if (!buttonsContainerElement) {
                buttonsContainerElement = create('gallery_buttons');
            }

            if (imagesPrevNextButtonsEnabled) {
                if (!buttonPrevious) {
                    if (imagesPrevNextButtonsSeparated) {
                        if (!prevButtonContainerElement) {
                            prevButtonContainerElement = create('gallery_button_previous_container');
                        }
                        buttonPrevious = new GalleryPreviousButtonComponent(galleryInfo);
                        prevButtonContainerElement.appendChild(buttonPrevious.getComponentElement());
                    } else {
                        buttonPrevious = new GalleryPreviousButtonComponent(galleryInfo);
                        buttonsContainerElement.appendChild(buttonPrevious.getComponentElement());
                    }
                }
            } else if (buttonPrevious) {
                if (prevButtonContainerElement) {
                    componentElement.removeChild(prevButtonContainerElement);
                }
                buttonPrevious.destroy();
                buttonPrevious = null;
            }

            if (imagesButtonsEnabled) {
                if (imageButtons.length === 0) {
                    for (var i = 0; i <= imagesInfosList.length; i++) {
                        if (imagesInfosList[i]) {
                            button = new GalleryButtonComponent(imagesInfosList[i], galleryInfo);
                            buttonsContainerElement.appendChild(button.getComponentElement());
                            imageNumber++;
                            imageButtons.push(button);
                        }
                    }
                }
            } else if (imageButtons) {
                destroyImageButtons();
            }

            if (imagesPrevNextButtonsEnabled) {
                if (!buttonNext) {
                    if (imagesPrevNextButtonsSeparated) {
                        if (!nextButtonContainerElement) {
                            nextButtonContainerElement = create('gallery_button_next_container');
                        }
                        buttonNext = new GalleryNextButtonComponent(galleryInfo);
                        nextButtonContainerElement.appendChild(buttonNext.getComponentElement());
                    } else {
                        buttonNext = new GalleryNextButtonComponent(galleryInfo);
                        buttonsContainerElement.appendChild(buttonNext.getComponentElement());
                    }
                }
            } else if (buttonNext) {
                if (nextButtonContainerElement) {
                    componentElement.removeChild(nextButtonContainerElement);
                }
                buttonNext.destroy();
                buttonNext = null;
            }

            if (!playBackButton && playbackButtonEnabled) {
                playBackButton = new GalleryPlaybackButtonComponent(galleryInfo);
                buttonsContainerElement.appendChild(playBackButton.getComponentElement());
                imageButtons.push(playBackButton);
            }
            if (!fullScreenButton && fullScreenButtonEnabled) {
                fullScreenButton = new GalleryFullScreenButtonComponent(galleryInfo, imagesComponent);
                buttonsContainerElement.appendChild(fullScreenButton.getComponentElement());
                imageButtons.push(fullScreenButton);
            }
        } else if (buttonsContainerElement) {
            if (buttonsContainerElement.parentNode){
                buttonsContainerElement.parentNode.removeChild(buttonsContainerElement);
            }
            buttonsContainerElement = null;
            destroyImageButtons();
            if (buttonPrevious) {
                buttonPrevious.destroy();
                buttonPrevious = null;
            }
            if (buttonNext) {
                buttonNext.destroy();
                buttonNext = null;
            }
        }

    };
    var destroyImageButtons = function() {
        for (var i = 0; i < imageButtons.length; i++) {
            imageButtons[i].destroy();
        }
        imageButtons = [];
    };
    var create = function(className) {
        //we add new element to componentElement or to div.gallery_structure if it exists in html
        //if element with className already defined in html(as div.gallery_structure child or sub child) return him
        var newElement = document.createElement('div');
        newElement.className = className;

        if (preloadedStructureElement) {
            var definedElement = findChild(preloadedStructureElement, className);
            if (definedElement) {
                newElement = definedElement;
            } else {
                preloadedStructureElement.appendChild(newElement);
            }
        } else {
            componentElement.appendChild(newElement);
        }

        return newElement;
    };

    var findChild = function(element, className) {
        for (var i = 0; i < element.childNodes.length; i++) {
            var child = element.childNodes[i];
            if ((typeof child.className !== 'undefined') && (child.className.indexOf(className) >= 0)) {
                return child;
            }
            var result;
            if (result = findChild(child, className)) {
                return result;
            }
        }
        return false;
    };
    this.destroy = function() {
        eventsManager.removeHandler(window, 'resize', self.recalculateSizes);
        controller.removeListener('startApplication', imagesComponent.startApplication);

        if (imagesComponent) {
            imagesComponent.destroy();
            imagesComponent = null;
        }
        if (buttonPrevious) {
            buttonPrevious.destroy();
            buttonPrevious = null;
        }
        if (buttonNext) {
            buttonNext.destroy();
            buttonNext = null;
        }
        if (descriptionComponent) {
            descriptionComponent.destroy();
            descriptionComponent = null;
        }
        destroyImageButtons();
    };
    this.getImagesComponent = function() {
        return imagesComponent;
    };
    this.getDescriptionComponent = function() {
        return descriptionComponent;
    };
    this.getButtonsContainer = function() {
        return buttonsContainerElement;
    };
    this.getSelectorComponent = function() {
        return selectorComponent;
    };
    this.recalculateSizes = function() {
        var imagesComponentHeight;
        var computedStyle;
        if (typeof window.getComputedStyle !== 'undefined') {
            computedStyle = window.getComputedStyle(componentElement);
        } else {
            computedStyle = componentElement.currentStyle;
        }
        var galleryWidth = componentElement.offsetWidth - parseFloat(computedStyle.paddingLeft) - parseFloat(computedStyle.paddingRight);
        var galleryHeight;
        var galleryHeightSetting = galleryInfo.getGalleryHeight(window.mobileLogics.isPhoneActive());
        var galleryResizeType = galleryInfo.getGalleryResizeType(window.mobileLogics.isPhoneActive());
        if (galleryResizeType === 'imagesHeight') {
            imagesComponentHeight = galleryHeightSetting;
        } else if (galleryResizeType === 'aspected') {
            //galleryHeightSetting here contains aspect ratio
            imagesComponentHeight = galleryWidth * galleryHeightSetting;
        } else if (galleryResizeType === 'viewport') {
            var viewPortHeight = window.innerHeight ? window.innerHeight : document.documentElement.offsetHeight;
            galleryHeight = galleryHeightSetting;
            if (galleryHeight && (typeof galleryHeight === 'string') && galleryHeight.indexOf('%') > -1) {
                galleryHeight = viewPortHeight * parseFloat(galleryHeight) / 100;
            } else {
                galleryHeight = viewPortHeight * galleryHeight;
            }
        } else {
            galleryHeight = galleryHeightSetting;
            if (!galleryHeight) {
                galleryHeight = componentElement.offsetHeight - parseFloat(computedStyle.paddingTop) - parseFloat(computedStyle.paddingBottom);
            }
        }
        if (galleryHeight) {
            if (selectorComponent) {
                var selectorHeight = galleryInfo.getThumbnailsSelectorHeight();
                if (selectorHeight) {
                    if (selectorHeight.indexOf('%') > -1) {
                        selectorHeight = galleryHeight * parseFloat(selectorHeight) / 100;
                    }
                    selectorComponent.setSizes(galleryWidth, selectorHeight);
                }
                imagesComponentHeight = galleryHeight - selectorComponent.getGalleryHeight();
            } else {
                imagesComponentHeight = galleryHeight;
            }
        }
        imagesComponent.setSizes(galleryWidth, imagesComponentHeight);
    };
    this.getButtonNextComponent = function() {
        return buttonNext;
    };
    this.getButtonPreviousComponent = function() {
        return buttonPrevious;
    };
    construct();
};