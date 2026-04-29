window.ScrollPagesMixin = function() {
    this.sp_selectedNumber = -1;
    this.sp_rotateDelay = 4500;
    this.sp_interval = null;

    this.sp_effectDuration = 3;
    this.sp_pageElements = [];
    this.sp_componentElement = null;
    this.sp_onPageChangeCallback = false;
    this.sp_autoStart = false;
    this.sp_preloadedImagesIndex = {};
    this.preloadCallBack = null;

    this.scrollPagesInit = function(options) {
        parseOptions.call(this, options);
        if (this.sp_componentElement) {
            this.sp_componentElement.scrollLeft = 0;
            var scope = this;
            if (this.sp_pageElements.length) {
                if (this.sp_rotateDelay > 0 && this.sp_autoStart) {
                    this.startPagesRotation();
                }
            }
            eventsManager.addHandler(window, 'resize', function(event) {
                return onWindowResize.call(scope, event);
            });
        }
    };

    var parseOptions = function(options) {
        if (typeof options.componentElement !== 'undefined') {
            this.sp_componentElement = options.componentElement;
        }
        if (typeof options.rotateDelay !== 'undefined') {
            this.sp_rotateDelay = options.rotateDelay;
        }
        if (typeof options.onPageChangeCallback !== 'undefined') {
            this.sp_onPageChangeCallback = options.onPageChangeCallback;
        }
        if (typeof options.effectDuration !== 'undefined') {
            this.sp_effectDuration = options.effectDuration;
        }
        if (typeof options.pageElements !== 'undefined') {
            this.sp_pageElements = options.pageElements;
        }
        if (typeof options.autoStart !== 'undefined') {
            this.sp_autoStart = options.autoStart;
        }
        if (typeof options.preloadCallBack !== 'undefined') {
            this.preloadCallBack = options.preloadCallBack;
        }
    };
    this.showPage = function(newNumber) {
        if (this.preloadCallBack) {
            var startPage = Math.min(newNumber, this.sp_selectedNumber);
            var endPage = Math.max(newNumber, this.sp_selectedNumber);
            var page;
            for (page = startPage; page <= endPage; page++) {
                if ((page !== -1) && !this.sp_preloadedImagesIndex[page]) {
                    this.sp_preloadedImagesIndex[page] = false;
                }
            }
            for (page = startPage; page <= endPage; page++) {
                this.preloadCallBack(page, function(scope, preloadPage, newNumber) {
                    return function() {
                        checkPreloadedImages.call(scope, preloadPage, newNumber);
                    };
                }(this, page, newNumber));
            }
        } else {
            showPageInside.call(this, newNumber);
        }
    };

    var checkPreloadedImages = function(preloadedImageNumber, newNumber) {
        this.sp_preloadedImagesIndex[preloadedImageNumber] = true;
        this.sp_pageElements[preloadedImageNumber].style.display = '';

        var allPreloaded = true;
        for (var pageNumber in this.sp_preloadedImagesIndex) {
            if (!this.sp_preloadedImagesIndex[pageNumber]) {
                allPreloaded = false;
                break;
            }
        }
        if (allPreloaded) {
            showPageInside.call(this, newNumber);
        }
    };

    var showPageInside = function(newNumber) {
        if (newNumber != this.sp_selectedNumber && this.sp_pageElements[newNumber]) {
            var newPage = this.sp_pageElements[newNumber];
            var endScrollLeft = newPage.offsetLeft;
            TweenLite.to(this.sp_componentElement, this.sp_effectDuration, {'scrollLeft': endScrollLeft});

            this.sp_selectedNumber = newNumber;
            if (typeof this.sp_onPageChangeCallback == 'function') {
                this.sp_onPageChangeCallback(this.sp_selectedNumber);
            }
        }
    };
    this.startPagesRotation = function() {
        var scope = this;
        this.sp_interval = window.setInterval(function() {
            return scope.showNextPage.call(scope);
        }, this.sp_rotateDelay);
    };
    this.stopPagesRotation = function() {
        window.clearInterval(this.sp_interval);
    };
    this.showNextPage = function() {
        var newNumber = this.sp_selectedNumber + 1;
        if (newNumber === this.sp_pageElements.length) {
            newNumber = 0;
        }
        this.showPage(newNumber);
    };
    this.showPreviousPage = function() {
        var newNumber = this.sp_selectedNumber - 1;
        if (newNumber < 0) {
            newNumber = this.sp_pageElements.length - 1;
        }
        this.showPage(newNumber);
    };
    var onWindowResize = function() {
        TweenLite.killTweensOf(this.sp_componentElement);
        if (this.sp_pageElements[this.sp_selectedNumber]) {
            this.sp_componentElement.scrollLeft = this.sp_pageElements[this.sp_selectedNumber].offsetLeft;
        }
    };

    /**
     *
     * @param options
     *
     * @deprecated - use scrollPagesInit instead
     */
    this.spInit = function(options) {
        this.scrollPagesInit(options);
    };
    /**
     *
     * @deprecated - use stopPagesRotation instead
     */
    this.spStopRotation = function() {
        this.stopPagesRotation();
    };
    /**
     *
     * @deprecated - use startPagesRotation instead
     */
    this.spStartRotation = function() {
        this.startPagesRotation();
    };

    return this;
};