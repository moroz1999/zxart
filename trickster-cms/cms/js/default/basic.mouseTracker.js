window.mouseTracker = new function() {
    var self = this;
    this.mouseX = 0;
    this.mouseY = 0;
    this.captureMouseCoordinates = false;
    var pixelRatio = -1;

    this.init = function() {
        self.captureMouseCoordinates = captureMouseCoordinates_standards;
        window.eventsManager.addHandler(document, 'mousemove', this.captureMouseCoordinates);
        window.eventsManager.addHandler(window, 'resize', resized);
    };
    var resized = function() {
        // for page zoom changes handling
        // without this previously recorded coords are invalid until next mouse move
        if (pixelRatio < 0) {
            // mouse hasn't even been moved yet, coords are unknown
            return;
        }
        var newPixelRatio = window.devicePixelRatio || 1;
        if (newPixelRatio == pixelRatio) {
            return;
        }
        self.mouseX *= pixelRatio / newPixelRatio;
        self.mouseY *= pixelRatio / newPixelRatio;
        pixelRatio = newPixelRatio;
    };
    var captureMouseCoordinates_standards = function(event) {
        var mouseX = event.pageX;
        var mouseY = event.pageY;

        if (mouseX < 0) {
            mouseX = 0;
        }
        if (mouseY < 0) {
            mouseY = 0;
        }
        self.mouseX = mouseX;
        self.mouseY = mouseY;
        pixelRatio = window.devicePixelRatio || 1;
    };
    this.getDelta = function(event) {
        var delta = 0;
        if (event.wheelDelta) {
            delta = event.wheelDelta / 120;
        } else if (event.detail) {
            delta = -event.detail / 3;
        }
        return delta;
    };
    this.getElementCoordinates = function(domElement) {
        var curleft = curtop = 0;
        if (domElement.offsetParent) {
            var curleft = domElement.offsetLeft;
            var curtop = domElement.offsetTop;
            while (domElement = domElement.offsetParent) {
                if (domElement.tagName != 'body' && domElement.tagName != 'BODY') {
                    curleft += domElement.offsetLeft - domElement.scrollLeft;
                    curtop += domElement.offsetTop - domElement.scrollTop;
                } else {
                    curleft += domElement.offsetLeft;
                    curtop += domElement.offsetTop;
                }
            }
        }
        return {left: curleft, top: curtop};
    };

    this.init();
};

window.customMouseTracker = function(documentObject) {
    this.init = function() {
        self.captureMouseCoordinates = captureMouseCoordinates_standards;
        if (navigator.appName == 'Microsoft Internet Explorer') {
            var version = navigator.appVersion.match(/MSIE ([\d.]+);/)[1];
            if (version < 9) {
                self.captureMouseCoordinates = captureMouseCoordinates_ie;
            }
        }

        if (documentObject) {
            this.documentObject = documentObject;
            if (documentObject.contentWindow) {
                eventsManager.addHandler(documentObject.contentWindow.document, 'mousemove', this.captureMouseCoordinates);
            } else {
                eventsManager.addHandler(documentObject, 'mousemove', this.captureMouseCoordinates);
            }
        }
    };
    var captureMouseCoordinates_standards = function(event) {
        var mouseX = event.pageX;
        var mouseY = event.pageY;

        if (mouseX < 0) {
            mouseX = 0;
        }
        if (mouseY < 0) {
            mouseY = 0;
        }

        self.mouseX = mouseX;
        self.mouseY = mouseY;
    };
    var captureMouseCoordinates_ie = function(event) {
        var mouseX = self.documentObject.contentWindow.event.clientX;
        var mouseY = self.documentObject.contentWindow.event.clientY;

        if (mouseX < 0) {
            mouseX = 0;
        }
        if (mouseY < 0) {
            mouseY = 0;
        }

        self.mouseX = mouseX;
        self.mouseY = mouseY;
    };

    var self = this;
    this.mouseX = 0;
    this.mouseY = 0;
    this.documentObject = false;
    this.captureMouseCoordinates = false;

    this.init();
};