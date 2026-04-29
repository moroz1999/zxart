window.SlidesMixin = function() {
    this.sl_intervalId = null;
    this.sl_selectedNumber = -1;
    this.sl_slideElements = false;
    this.sl_displaySlideAnim = null;
    this.sl_hideSlideAnim = null;

    // configurable properties
    this.sl_autoStart = false;
    this.sl_componentElement = false;
    this.sl_onSlideChange = null;
    this.sl_interval = 2000;
    this.sl_changeDuration = 0;
    this.sl_heightCalculated = true;
    this.preloadCallBack = null;

    this.initSlides = function(options) {
        var scope = this;
        parseOptions.call(scope, options);
        if (this.sl_componentElement) {
            if (this.sl_slideElements.length) {
                if (this.sl_heightCalculated) {
                    calculateHeight.call(scope);
                    eventsManager.addHandler(window, 'resize', function(event) {
                        return calculateHeight.call(scope, event);
                    });
                }

                // start automatic slideshow
                if (this.sl_interval && this.sl_autoStart) {
                    this.startSlideShow();
                }
            }
        }
    };

    var calculateHeight = function() {
        var height = 0;
        for (var i = 0, l = this.sl_slideElements.length; i !== l; i++) {
            var slideHeight = this.sl_slideElements[i].offsetHeight;
            if (height < slideHeight) {
                height = slideHeight;
            }
        }
        this.sl_componentElement.style.height = height + 'px';
    };

    this.showSlide = function(newNumber) {
        if (this.preloadCallBack) {
            this.preloadCallBack(newNumber, function(scope, number) {
                return function() {
                    showSlideInside.call(scope, number);
                };
            }(this, newNumber));
        } else {
            var scope = this;
            showSlideInside.call(scope, newNumber);
        }
    };
    var showSlideInside = function(newNumber) {
        if (newNumber != this.sl_selectedNumber && this.sl_slideElements[newNumber]) {
            for (var i = 0; i < this.sl_slideElements.length; i++) {
                if ((i != this.sl_selectedNumber) && (i != newNumber)) {
                    this.sl_slideElements[i].style.display = 'none';
                    this.sl_slideElements[i].style.opacity = 0;
                }
            }

            if (this.sl_displaySlideAnim) {
                this.sl_displaySlideAnim.kill();
            }
            if (this.sl_hideSlideAnim) {
                this.sl_hideSlideAnim.kill();
            }
            this.sl_slideElements[newNumber].style.zIndex = '5';
            this.sl_slideElements[newNumber].style.display = 'block';
            this.sl_slideElements[newNumber].style.opacity = 0;

            if (this.sl_selectedNumber === -1) {
                // make first slide visible without the FX
                this.sl_slideElements[newNumber].style.opacity = 1;
            } else {
                this.sl_slideElements[this.sl_selectedNumber].style.zIndex = '1';

                this.sl_displaySlideAnim = TweenLite.to(this.sl_slideElements[newNumber], this.sl_changeDuration, {
                    'css': {'opacity': 1},
                    'onComplete': function(scope, oldNumber) {
                        return function() {
                            slideCompleteHandler.call(scope, oldNumber);
                        };
                    }(this, this.sl_selectedNumber),
                });
                this.sl_hideSlideAnim = TweenLite.to(this.sl_slideElements[this.sl_selectedNumber], this.sl_changeDuration, {'css': {'opacity': 0}});
            }
            this.sl_selectedNumber = newNumber;

            if (typeof this.sl_onSlideChange == 'function') {
                this.sl_onSlideChange(this.sl_selectedNumber);
            }
        }
    };
    var slideCompleteHandler = function(oldNumber) {
        if (this.sl_slideElements[oldNumber] && oldNumber != this.sl_selectedNumber) {
            // this.sl_slideElements[oldNumber].style.display = "none";
        }
    };

    this.showNextSlide = function() {
        if (this.sl_componentElement.offsetHeight > 0) {
            var newNumber = this.sl_selectedNumber + 1;
            if (newNumber == this.sl_slideElements.length) {
                newNumber = 0;
            }
            this.showSlide(newNumber);
        }
    };

    this.showPreviousSlide = function() {
        if (this.sl_componentElement.offsetHeight > 0) {
            var newNumber = this.sl_selectedNumber - 1;
            if (newNumber < 0) {
                newNumber = this.sl_slideElements.length - 1;
            }
            this.showSlide(newNumber);
        }
    };

    var parseOptions = function(options) {
        if (typeof options.componentElement !== 'undefined') {
            this.sl_componentElement = options.componentElement;
        }
        if (typeof options.onSlideChange !== 'undefined') {
            this.sl_onSlideChange = options.onSlideChange;
        }
        if (typeof options.interval !== 'undefined') {
            this.sl_interval = options.interval;
        }
        if (typeof options.changeDuration !== 'undefined') {
            this.sl_changeDuration = options.changeDuration;
        }
        if (typeof options.heightCalculated !== 'undefined') {
            this.sl_heightCalculated = options.heightCalculated;
        }
        if (typeof options.slideElements !== 'undefined') {
            this.sl_slideElements = options.slideElements;
        }
        if (typeof options.sp_autoStart !== 'undefined') {
            this.sl_autoStart = options.sp_autoStart;
        }
        if (typeof options.preloadCallBack !== 'undefined') {
            this.preloadCallBack = options.preloadCallBack;
        }
    };

    this.startSlideShow = function(immediateSwitch) {
        controller.fireEvent('slidesPlaybackUpdate', this.sl_componentElement);

        if (immediateSwitch) {
            this.showNextSlide();
        }
        var scope = this;
        this.sl_intervalId = window.setInterval(function() {
            return scope.showNextSlide.apply(scope);
        }, this.sl_interval);
    };

    this.stopSlideShow = function() {
        controller.fireEvent('slidesPlaybackUpdate', this.sl_componentElement);
        window.clearInterval(this.sl_intervalId);
    };

    return this;
};