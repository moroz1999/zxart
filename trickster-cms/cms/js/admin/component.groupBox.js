window.GroupBoxComponent = function(domElement, containerElement) {
    this.init = function() {
        self.domElement = domElement;
        self.checked = self.domElement.checked;
        eventsManager.addHandler(self.domElement, 'change', self.change);
    };
    this.change = function() {
        if (self.checked) {
            self.checked = false;
            self.domElement.checked = false;
        } else {
            self.checked = true;
            self.domElement.checked = true;
        }

        var elements = _('.singlebox', containerElement);
        for (var i = 0; i < elements.length; i++) {
            elements[i].checked = self.checked;
            eventsManager.fireEvent(elements[i], 'change');
        }
    };
    var self = this;
    this.checked = true;
    this.domElement = false;
    this.init();
};