window.ToggleableContainer = function(componentElement, options) {
    var self = this;
    this.componentElement = componentElement;
    var afterOpenCallback;
    var visible = false;
    var contentElement, markerElement;
    var init = function() {

        self.parseOptions.call(self, options);

        new ToggleableContainerTriggerComponent(self);

        if (contentElement.className.indexOf('toggleable_component_content_hidden') >= 0) {
            contentElement.style.height = 0;
            if (markerElement) {
                markerElement.className = markerElement.className.replace('toggleable_component_marker', 'toggleable_component_marker_shrinked');
            }
        } else {
            visible = true;
        }
    };

    this.parseOptions = function(options) {
        if (typeof options === 'undefined') {
            contentElement = componentElement.querySelector('.toggleable_component_content');
            markerElement = componentElement.querySelector('.toggleable_component_marker');
        } else {
            if (typeof options.contentElement === 'undefined') {
                contentElement = componentElement.querySelector('.toggleable_component_content', componentElement);
            } else {
                contentElement = options.contentElement;
            }
            if (typeof options.markerElement === 'undefined') {
                markerElement = componentElement.querySelector('.toggleable_component_marker', componentElement);
            } else {
                markerElement = options.markerElement;
            }
            if (typeof options.defaultBehaviour !== 'undefined') {
                if (options.defaultBehaviour == 'shown' && typeof options.afterOpenCallback !== 'undefined') {
                    options.afterOpenCallback.call();
                }
            }
            if (typeof options.afterOpenCallback !== 'undefined') {
                afterOpenCallback = options.afterOpenCallback;
            }
        }
    };

    this.toggle = function() {
        if (!visible) {
            this.show();
        } else {
            this.hide();
        }
    };
    this.hide = function() {
        visible = false;
        TweenLite.to(contentElement, 0.5, {'css': {'height': 0, 'opacity': 0}, 'onComplete': finishHide});
    };
    var finishHide = function() {
        domHelper.addClass(componentElement, 'toggleable_component_collapsed');
        domHelper.addClass(contentElement, 'toggleable_component_content_hidden');
    };
    this.show = function() {
        visible = true;
        domHelper.removeClass(componentElement, 'toggleable_component_collapsed');
        domHelper.removeClass(contentElement, 'toggleable_component_content_hidden');

        TweenLite.to(contentElement, 0.5, {'css': {'height': contentElement.scrollHeight, 'opacity': 1}});
        if (afterOpenCallback) {
            afterOpenCallback.call();
        }

    };
    init();
};

window.ToggleableContainerTriggerComponent = function(parentElement) {
    var self = this;
    this.componentElement = null;

    var init = function() {
        self.componentElement = parentElement.componentElement.querySelector('.toggleable_component_trigger');
        eventsManager.addHandler(self.componentElement, 'click', clickHandler);
    };
    var clickHandler = function() {
        parentElement.toggle();
    };
    init();
};