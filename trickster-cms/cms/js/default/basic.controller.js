controller = new function() {
    var init = function() {
        eventsManager.addHandler(window, 'DOMContentLoaded', domLoadedHandler);
        eventsManager.addHandler(window, 'load', onloadHandler);
    };
    var domLoadedHandler = function() {
        domLoaded = true;
        self.fireEvent('initLogics');
        self.fireEvent('initDom');
        self.fireEvent('startApplication');
    };
    var onloadHandler = function() {
        if (!domLoaded) {
            self.fireEvent('initLogics');
            self.fireEvent('initDom');
            self.fireEvent('startApplication');
        }
        self.fireEvent('DOMContentReady');

    };
    this.addListener = function(eventName, listener) {
        var listenerExists = false;
        if (!eventsIndex[eventName]) {
            eventsIndex[eventName] = [];
        }

        for (var i = 0; i < eventsIndex[eventName].length; i++) {
            if (eventsIndex[eventName][i] == listener) {
                listenerExists = true;
            }
        }

        if (!listenerExists) {
            eventsIndex[eventName].push(listener);
        }
    };
    this.fireEvent = function(eventName, argument) {
        if (typeof argument == 'undefined') {
            argument = false;
        }
        if (eventsIndex[eventName]) {
            //handlers list should be traversed in reversed order in case some handler gets dynamically removed during the cycle
            eventsIndex[eventName].reverse();
            for (var i = eventsIndex[eventName].length - 1; i >= 0; i--) {
                if (typeof eventsIndex[eventName][i] == 'function') {
                    eventsIndex[eventName][i](argument);
                }
            }
            eventsIndex[eventName].reverse();
        }
    };
    this.removeListener = function(eventName, listener) {
        if (eventsIndex[eventName]) {
            for (var i = 0; i < eventsIndex[eventName].length; i++) {
                if (eventsIndex[eventName][i] == listener) {
                    eventsIndex[eventName].splice(i, 1);
                }
            }
        }
    };

    var self = this;
    var domLoaded = false;
    var eventsIndex = {};

    init();
};