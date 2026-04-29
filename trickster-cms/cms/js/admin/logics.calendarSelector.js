window.calendarSelectorLogics = new function() {
    this.init = function() {
        var inputElements = _('.input_date');
        for (var i = 0; i < inputElements.length; i++) {
            self.calendarObjects.push(new CalendarSelectorComponent({'inputElement': inputElements[i]}));
        }

        window.eventsManager.addHandler(document, 'click', self.hideCalendars);
    };
    this.hideCalendars = function() {
        for (var i = 0; i < self.calendarObjects.length; i++) {
            var element = self.calendarObjects[i];
            if (element.state != 'closed') {
                element.hideCalendarElement();
            }
        }
    };
    this.getCalendar = function(parameters) {
        var calendar = new CalendarSelectorComponent(parameters);
        self.calendarObjects.push(calendar);
        return calendar;
    };

    var self = this;
    this.calendarObjects = new Array();
    controller.addListener('initDom', this.init);
};