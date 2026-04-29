window.LazyLoadingMixin = function() {
    this.scrollCheckInterval = null;
    this.hasScrolled = false;
    this.lazyLoadingObserver = false;
    this.componentElement = null;
    this.displayCallback = null;

    this.initLazyLoading = function(options) {
        this.componentElement = options.componentElement;
        this.displayCallback = options.displayCallback;
        let isIE11 = !!window.MSInputMethodContext && !!document.documentMode || typeof IntersectionObserver === 'undefined';
        if (isIE11) {
            if (!(checkOnScreen.bind(this)())) {
                window.addEventListener('scroll', scrollHandler.bind(this));
                this.scrollCheckInterval = setInterval(checkIfScrolled.bind(this), 250);
            }
        } else {
            this.lazyLoadingObserver = new IntersectionObserver(checkObserver.bind(this), {threshold: 0.01});
            this.lazyLoadingObserver.observe(this.componentElement);
        }
    };

    const scrollHandler = function() {
        this.hasScrolled = true;
    };

    const checkIfScrolled = function() {
        if (this.hasScrolled) {
            this.hasScrolled = false;
            checkOnScreen.bind(this)();
        }
    };

    const checkOnScreen = function() {
        var isOnScreen = this.isOnScreen(this.componentElement, 1.2);
        if (isOnScreen) {
            display.bind(this)();
        }
        return isOnScreen;
    };

    const checkObserver = function(entries) {
        for (let i = 0; i < entries.length; i++) {
            if (entries[i].isIntersecting) {
                display.bind(this)();
                break;
            }
        }
    };
    const display = function() {
        if (this.lazyLoadingObserver) {
            this.lazyLoadingObserver.unobserve(this.componentElement);
        }
        if (this.scrollCheckInterval) {
            window.removeEventListener('scroll', scrollHandler);
            clearInterval(this.scrollCheckInterval);
        }
        this.displayCallback();
    };

    DomHelperMixin.call(this);
};
