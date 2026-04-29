window.DomElementMakerMixin = function() {
    this.makeElement = function(tag, properties, parent) {
        var e = document.createElement(tag);

        if (properties) {
            if (typeof properties === 'string') {
                e.className = properties;
            } else {
                for (var key in properties) {
                    e[key] = properties[key];
                }
            }
        }
        if (parent) {
            parent.appendChild(e);
        }
        return e;
    };
    return this;
};