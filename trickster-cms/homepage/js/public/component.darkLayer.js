window.DarkLayerComponent = new function() {
    this.showLayer = function(onclickFunction, callback, allowClose) {
        if (this.domElement) {
            this.domElement.style.opacity = 0;
            this.domElement.style.display = 'block';

            this.domElement.style.top = '0';
            this.domElement.style.bottom = '0';
            this.domElement.style.left = '0';
            this.domElement.style.right = '0';

            if (this.darkLayerComponentOpacity) {
                this.fullOpacity = this.darkLayerComponentOpacity;
            }
            if (callback) {
                TweenLite.to(this.domElement, 0.2, {'css': {'opacity': this.fullOpacity}, 'onComplete': callback});
            } else {
                TweenLite.to(this.domElement, 0.2, {'css': {'opacity': this.fullOpacity}});
            }
            if (allowClose != null) {
                this.allowClose = allowClose;
            }
            if (onclickFunction) {
                window.eventsManager.addHandler(this.domElement, eventsManager.getPointerStartEventName(), onclickFunction);
            } else {
                window.eventsManager.addHandler(this.domElement, eventsManager.getPointerStartEventName(), this.layerClickHandler);
            }
        }
    };
    this.hideLayer = function() {
        if (self.allowClose) {
            TweenLite.to(self.domElement, 0.2, {'css': {'opacity': 0}, 'onComplete': self.layerClickHandlerStyle});
        }
    };
    this.layerClickHandler = function() {
        self.hideLayer();
    };
    this.forceHideLayer = function(callback) {
        self.closeCallBack = callback;
        self.allowClose = true;
        self.hideLayer();
    };
    this.layerClickHandlerStyle = function() {
        self.domElement.style.display = 'none';
        if (self.closeCallBack) {
            var callBack = self.closeCallBack;
            self.closeCallBack = false;
            callBack();
        }
    };
    this.init = function() {
        if (self.domElement == null) {
            var domElement = document.createElement('div');
            domElement.className = 'dark_layer';
            domElement.style.backgroundColor = self.backgroundColor;
            domElement.style.position = 'fixed';
            domElement.style.top = '0';
            domElement.style.left = '0';
            domElement.style.zIndex = '90';
            domElement.style.display = 'none';
            self.domElement = domElement;
            document.body.appendChild(domElement);
        }
    };

    var self = this;
    this.closeCallBack = false;
    this.domElement = null;
    this.fullOpacity = 0.6;
    this.step = 0.03;
    this.allowClose = true;
    this.backgroundColor = '#000';
    controller.addListener('initDom', this.init);
};