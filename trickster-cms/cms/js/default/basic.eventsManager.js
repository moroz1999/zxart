window.eventsManager = new function() {
    var self = this;

    this.addHandler = false;
    this.fireEvent = false;
    var mouseEnterSupported;
    var mouseLeaveSupported;
    var eventsSet;

    var init = function() {
        if (typeof document.documentElement.onmouseenter == 'object') {
            mouseEnterSupported = true;
        }
        if (typeof document.documentElement.onmouseleave == 'object') {
            mouseLeaveSupported = true;
        }
        self.fireEvent = fireEvent_standards;
        self.addHandler = addHandler_standards;
        if (navigator.appName == 'Microsoft Internet Explorer') {
            if (navigator.appVersion.match(/MSIE ([\d.]+);/)) {
                var version = navigator.appVersion.match(/MSIE ([\d.]+);/)[1];
                if (version < 9) {
                    self.fireEvent = fireEvent_ie;
                    self.addHandler = addHandler_ie;
                } else {
                    self.addHandler = addHandler_ie9;
                }
            }
        }
    };
    this.getEventTarget = function(event) {
        var eventElement = null;
        if (event.target) {
            eventElement = event.target;
        } else if (event.srcElement) {
            eventElement = event.srcElement;
        }
        return eventElement;
    };
    var addHandler_ie9 = function(object, event, handler, useCapture) {
        if (!useCapture) {
            useCapture = false;
        }
        if (object == null || typeof object != 'object' && typeof object != 'function' || handler == null || typeof handler != 'function') {
            return false;
        } else {
            if (event == 'mousewheel') {
                object.addEventListener('DOMMouseScroll', handler, useCapture);
            }
            object.addEventListener(event, handler, false);
        }
    };
    var addHandler_standards = function(object, event, handler, useCapture) {
        if (!useCapture) {
            useCapture = false;
        }
        if (object == null || typeof object != 'object' && typeof object != 'function' || handler == null || typeof handler != 'function') {
            return false;
        } else {
            if (event === 'mouseenter' && !mouseEnterSupported) {
                object.addEventListener('mouseover', mouseEnter(handler), useCapture);
            } else if (event === 'mouseleave' && !mouseLeaveSupported) {
                object.addEventListener('mouseout', mouseEnter(handler), useCapture);
            } else if (event === 'mousewheel') {
                object.addEventListener('DOMMouseScroll', handler, useCapture);
            } else {
                object.addEventListener(event, handler, useCapture);
            }
        }
    };
    var addHandler_ie = function(object, event, handler) {
        if (object == null || typeof object != 'object' && typeof object != 'function' || handler == null || typeof handler != 'function') {
            return false;
        } else {
            if (object.attachEvent) {
                object.attachEvent('on' + event, handler);
            } else if (event === 'readystatechange') //this is for Internet Explorer, not supporting attachEvent on XMLHTTPRequest
            {
                object.onreadystatechange = handler;
            }
        }
    };
    var fireEvent_ie = function(object, eventName) {
        var eventObject = document.createEventObject();
        return object.fireEvent('on' + eventName, eventObject);
    };
    var fireEvent_standards = function(object, eventName) {
        var eventObject = document.createEvent('HTMLEvents');
        eventObject.initEvent(eventName, true, true);
        return !object.dispatchEvent(eventObject);
    };
    this.removeHandler = function(object, event, handler, useCapture) {
        if (!useCapture) {
            useCapture = false;
        }
        if (object.removeEventListener) {
            if (event == 'mousewheel') {
                object.removeEventListener('DOMMouseScroll', handler, useCapture);
            } else {
                object.removeEventListener(event, handler, useCapture);
            }
        } else if (object.detachEvent) {
            object.detachEvent('on' + event, handler);
        }
    };
    this.cancelBubbling = function(event) {
        event.cancelBubble = true;
        if (event.stopPropagation) {
            event.stopPropagation();
        }
    };
    this.preventDefaultAction = function(event) {
        if (event.preventDefault) {
            event.preventDefault();
        } else {
            event.returnValue = false;
        }

    };
    var mouseEnter = function(handler) {
        return function(event) {
            var relTarget = event.relatedTarget;
            if (this === relTarget || isAChildOf(this, relTarget)) {
                return;
            }
            handler.call(this, event);
        };
    };
    var isAChildOf = function(_parent, _child) {
        if (_parent === _child) {
            return false;
        }
        while (_child && _child !== _parent) {
            _child = _child.parentNode;
        }

        return _child === _parent;
    };
    this.detectTouchEventsSet = function() {
        if (!eventsSet) {
            eventsSet = 'unsupported';
            if (window.navigator.msPointerEnabled) {
                eventsSet = 'MSPointer';
            } else if (detectEventSupport('touchstart')) {
                eventsSet = 'touch';
            } else if (detectEventSupport('mousedown')) {
                eventsSet = 'mouse';
            }
        }

        return eventsSet;
    };
    var pointerStartEventName;
    this.getPointerStartEventName = function() {
        if (!pointerStartEventName) {
            var eventsSet = self.detectTouchEventsSet();
            if (eventsSet == 'MSPointer') {
                pointerStartEventName = 'MSPointerDown';
            } else if (eventsSet == 'touch') {
                pointerStartEventName = 'touchstart';
            } else if (eventsSet == 'mouse') {
                pointerStartEventName = 'mousedown';
            }
        }
        return pointerStartEventName;
    };
    var pointerEndEventName;
    this.getPointerEndEventName = function() {
        if (!pointerEndEventName) {
            var eventsSet = self.detectTouchEventsSet();
            if (eventsSet == 'MSPointer') {
                pointerEndEventName = 'MSPointerUp';
            } else if (eventsSet == 'touch') {
                pointerEndEventName = 'touchend';
            } else if (eventsSet == 'mouse') {
                pointerEndEventName = 'mouseup';
            }
        }
        return pointerEndEventName;
    };
    var pointerMoveEventName;
    this.getPointerMoveEventName = function() {
        if (!pointerMoveEventName) {
            var eventsSet = self.detectTouchEventsSet();
            if (eventsSet == 'MSPointer') {
                pointerMoveEventName = 'MSPointerMove';
            } else if (eventsSet == 'touch') {
                pointerMoveEventName = 'touchmove';
            } else if (eventsSet == 'mouse') {
                pointerMoveEventName = 'mousemove';
            }
        }
        return pointerMoveEventName;
    };
    var pointerCancelEventName;
    this.getPointerCancelEventName = function() {
        if (!pointerCancelEventName) {
            var eventsSet = self.detectTouchEventsSet();
            if (eventsSet == 'MSPointer') {
                pointerCancelEventName = 'MSPointerOut';
            } else if (eventsSet == 'touch') {
                pointerCancelEventName = 'touchcancel';
            } else if (eventsSet == 'mouse') {
                pointerCancelEventName = 'mouseleave';
            }
        }
        return pointerCancelEventName;
    };
    var detectEventSupport = function(eventName) {
        var element = document.createElement('div');
        var event = 'on' + eventName;
        var eventSupported = (event in element);
        if (!eventSupported) {
            element.setAttribute(event, 'return;');
            if (typeof element[event] == 'function') {
                eventSupported = true;
            }
        }
        return eventSupported;
    };
    init();
};