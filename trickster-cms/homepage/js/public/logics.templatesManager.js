window.templatesManager = new function() {
    var templates = {};

    var init = function() {
        if (typeof window.templates !== 'undefined') {
            templates = window.templates;
        }
    };
    this.get = function(name) {
        if (typeof templates[name] !== 'undefined') {
            return templates[name];
        } else {
            return 'Missing JS Template ' + name;
        }
    };
    controller.addListener('initLogics', init);
};