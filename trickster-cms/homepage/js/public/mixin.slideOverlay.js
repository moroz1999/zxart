window.SlideOverlayMixin = function() {
    this.so_overlayElement = null;
    this.so_acceleration = 0.6;
    this.so_overlayParentElement = null;
    this.so_visibleOffset = 0;
    this.so_enableMouseover = true;

    this.initSlideOverlay = function(options) {
        this.parseOptions(options);

        this.so_overlayElement.style.right = '0';
        this.so_overlayElement.style.left = '0';
        this.so_overlayElement.style.top = '100%';
        this.so_overlayElement.style.bottom = '0';
        this.so_overlayElement.style.display = 'block';
        this.so_overlayElement.style.position = 'absolute';
        this.so_overlayElement.style.overflow = 'visible';

        var scope = this;
        if (this.so_enableMouseover) {
            this.so_overlayParentElement.addEventListener('mouseenter', function(event) {
                return scope.showOverlay.call(scope, event);
            });
            this.so_overlayParentElement.addEventListener('mouseleave', function(event) {
                return scope.hideOverlay.call(scope, event);
            });
        }
    };

    this.showOverlay = function() {
        TweenLite.to(this.so_overlayElement, this.so_acceleration, {'css': {'top': this.so_visibleOffset}});
    };

    this.hideOverlay = function() {
        TweenLite.to(this.so_overlayElement, this.so_acceleration, {'css': {'top': '100%'}});
    };

    this.parseOptions = function(options) {
        if (typeof options.overlayElement != 'undefined') {
            this.so_overlayElement = options.overlayElement;
        }

        if (typeof options.so_overlayParentElement != 'undefined') {
            this.so_overlayParentElement = options.overlayParentElement;
        } else {
            this.so_overlayParentElement = this.so_overlayElement.parentNode;
        }
        if (typeof options.acceleration != 'undefined') {
            this.so_acceleration = options.acceleration;
        }
        if (typeof options.visibleOffset != 'undefined') {
            this.so_visibleOffset = options.visibleOffset;
        }
        if (typeof options.enableMouseover != 'undefined') {
            this.so_enableMouseover = options.enableMouseover;
        }
    };

    return this;
};