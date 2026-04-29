window.LazyImageComponent = function(componentElement) {
    let self = this;
    let init = function() {
        self.initLazyLoading({
            'componentElement': componentElement,
            'displayCallback': lazyLoadingCallback,
        });
    };
    const lazyLoadingCallback = function() {
        requestAnimationFrame(display);
    };
    const display = function() {
        componentElement.classList.remove('lazy_image');
        componentElement.src = componentElement.dataset.lazysrc;
        delete componentElement.dataset.lazysrc;
        if (componentElement.dataset.lazysrcset) {
            componentElement.srcset = componentElement.dataset.lazysrcset;
            delete componentElement.dataset.lazysrcset;
        }
    };
    init();
};
LazyLoadingMixin.call(LazyImageComponent.prototype);