window.DomHelperMixin = function() {
    // Finds the absolute position of an element on a page
    this.getPosition = function(element) {
        if (element.getBoundingClientRect) {
            var result = element.getBoundingClientRect();
            var scroll = this.getPageScroll();
            return {'x': result.left + scroll.x, 'y': result.top + scroll.y};
        } else {
            var curtop = 0;
            var curleft = 0;
            if (element.offsetParent) {
                do {
                    curleft += element.offsetLeft;
                    curtop += element.offsetTop;
                } while (element = element.offsetParent);
            }
            return {'x': curleft, 'y': curtop};
        }
    };

    // Finds the scroll position of a page
    this.getPageScroll = function() {
        var xScroll, yScroll;
        if (window.pageYOffset) {
            yScroll = window.pageYOffset;
            xScroll = window.pageXOffset;
        } else if (document.documentElement && document.documentElement.scrollTop) {
            yScroll = document.documentElement.scrollTop;
            xScroll = document.documentElement.scrollLeft;
        } else if (document.body) {// all other Explorers
            yScroll = document.body.scrollTop;
            xScroll = document.body.scrollLeft;
        }
        return {'x': xScroll, 'y': yScroll};
    };

    // Finds the position of an element relative to the viewport.
    this.getViewportRelativePosition = function(obj) {
        var elementPosition = this.getPosition(obj);
        var scroll = this.getPageScroll();
        return {'x': (elementPosition.x - scroll.x), 'y': (elementPosition.y - scroll.y)};
    };

    this.getWindowHeight = function() {
        return window.innerHeight ? window.innerHeight : document.documentElement.offsetHeight;
    };
    this.getWindowWidth = function() {
        return window.innerWidth ? window.innerWidth : document.documentElement.offsetWidth;
    };
    this.isOnScreen = function(obj, coefficient) {
        if (coefficient === undefined) {
            coefficient = 1;
        }
        return !(this.getViewportRelativePosition(obj).y > (this.getWindowHeight() * coefficient));
    };
};