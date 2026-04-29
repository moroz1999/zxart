window.CalendarSelectorComponent = function(parameters) {
    this.inputElement = null;
    this.startInputElement = null;
    this.endInputElement = null;
    var activeInputComponent = null;
    this.calendarElement = null;
    var headerElement;
    var daysElement;

    var todayStamp = null;
    var todayDate = null;
    var todayMonth = null;
    var todayYear = null;

    var currentMonth = null;
    var currentYear = null;

    // parameters
    this.position = null;
    this.parentElement = null;
    this.size = null;
    this.showCloseButton = null;
    this.showWeekNumbers = false;
    this.checkShowEvents = false;
    this.hideHover = false;
    this.showWeekDays = false;
    this.disableInput = null;
    this.changeCallBack = null;
    this.disablePastDays = null;
    this.activeInputClassName = null;
    this.keepCalendarOpen = null;
    this.persistPeriod = null;  // if false, start date greater than end date can be selected,
    // but it resets the end date (and vice versa)

    this.monthPages = new Array();
    this.links = new Array();

    var self = this;
    var inputComponent;
    var startComponent;
    var endComponent;

    this.init = function() {
        self.position = 'input';
        self.size = 'auto';
        self.showCloseButton = false;
        self.disablePastDays = false;
        self.persistPeriod = false;

        if (typeof parameters !== 'undefined') {
            importParameters(parameters);
        }
        if (this.inputElement) {
            inputComponent = new CalendarSelectorInput(this.inputElement, self);
        } else if (this.startInputElement && this.endInputElement) {
            startComponent = new CalendarSelectorInput(this.startInputElement, self);
            endComponent = new CalendarSelectorInput(this.endInputElement, self);
        }
        window.eventsManager.addHandler(window, 'resize', this.adjustCalendarElement);
        if (window.showCalendarLogics) {
            var firstConcert = showCalendarLogics.getFirstConcert();
            if (firstConcert) {
                currentMonth = firstConcert['month'] - 1;
                currentYear = firstConcert['year'];
            }
        }
        if (self.keepCalendarOpen) {
            self.displayCalendarElement();
        }
    };
    var importParameters = function(parameters) {
        if (typeof parameters.position !== 'undefined' && parameters.position) {
            self.position = parameters.position;
        }
        if (typeof parameters.parentElement !== 'undefined' && parameters.parentElement) {
            self.parentElement = parameters.parentElement;
        }
        if (typeof parameters.inputElement !== 'undefined' && parameters.inputElement) {
            self.inputElement = parameters.inputElement;
        } else if (typeof parameters.startInputElement == 'object' && typeof parameters.endInputElement == 'object') {
            self.startInputElement = parameters.startInputElement;
            self.endInputElement = parameters.endInputElement;
        }
        if (typeof parameters.size !== 'undefined' && parameters.size) {
            self.size = parseFloat(parameters.size);
        }
        if (typeof parameters.showCloseButton !== 'undefined' && parameters.showCloseButton) {
            self.showCloseButton = true;
        }
        if (typeof parameters.disableInput !== 'undefined' && parameters.disableInput) {
            self.disableInput = true;
        }
        if (typeof parameters.showWeekNumbers !== 'undefined' && parameters.showWeekNumbers) {
            self.showWeekNumbers = true;
        }
        if (typeof parameters.showWeekDays !== 'undefined' && parameters.showWeekDays) {
            self.showWeekDays = true;
        }
        if (typeof parameters.hideHover !== 'undefined' && parameters.hideHover) {
            self.hideHover = true;
        }
        if (typeof parameters.checkShowEvents !== 'undefined' && parameters.checkShowEvents) {
            self.checkShowEvents = true;
        }
        if (typeof parameters.changeCallBack !== 'undefined') {
            self.changeCallBack = parameters.changeCallBack;
        }
        if (typeof parameters.disablePastDays !== 'undefined') {
            self.disablePastDays = parameters.disablePastDays;
        }
        if (typeof parameters.activeInputClassName !== 'undefined') {
            self.activeInputClassName = parameters.activeInputClassName;
        }
        if (typeof parameters.keepCalendarOpen !== 'undefined') {
            self.keepCalendarOpen = parameters.keepCalendarOpen;
        }
        if (typeof parameters.persistPeriod !== 'undefined') {
            self.persistPeriod = parameters.persistPeriod;
        }
    };
    this.calendarElementClick = function(event) {
        window.eventsManager.cancelBubbling(event);
        window.eventsManager.preventDefaultAction(event);
    };

    this.displaySideCalendarElement = function() {
        window.calendarSelectorLogics.hideCalendars();

        var calendarElement = self.getCalendarElement();
        calendarElement.style.visibility = 'hidden';
        calendarElement.style.display = 'block';

        if (self.showCloseButton) {
            self.closeButton.style.display = 'block';
        } else {
            self.closeButton.style.display = 'none';
        }

        self.prepareCurrentValues();
        self.updateContents();
        self.adjustCalendarElement();
        calendarElement.style.visibility = 'visible';
    };

    this.displayCalendarElement = function() {
        window.calendarSelectorLogics.hideCalendars();

        self.state = 'opened';

        var calendarElement = self.getCalendarElement();
        calendarElement.style.visibility = 'hidden';
        calendarElement.style.display = 'block';

        if (self.showCloseButton) {
            self.closeButton.style.display = 'block';
        } else {
            self.closeButton.style.display = 'none';
        }

        self.prepareCurrentValues();
        self.updateContents();
        self.adjustCalendarElement();
        calendarElement.style.visibility = 'visible';
    };
    this.adjustCalendarElement = function() {
        var marginTop = 6;
        if (self.state == 'opened') {
            var calendarElement = self.getCalendarElement();
            if (self.position == 'input') {
                var coordinates = self.getActiveInputComponent().getElementPositions();
                calendarElement.style.position = 'absolute';
                calendarElement.style.left = coordinates.x + 'px';
                calendarElement.style.top = (coordinates.y + self.getActiveInputComponent().getInputHeight()) + marginTop + 'px';
            } else if (self.position == 'center') {
                var viewPortWidth = 0;
                var viewPortHeight = 0;
                if (window.innerHeight) {
                    viewPortWidth = window.innerWidth;
                    viewPortHeight = window.innerHeight;
                } else {
                    viewPortWidth = document.documentElement.offsetWidth;
                    viewPortHeight = document.documentElement.offsetHeight;
                }
                var componentWidth = calendarElement.offsetWidth;
                var componentHeight = calendarElement.offsetHeight;
                if (self.size != 'auto') {
                    componentWidth = viewPortWidth * self.size;
                }

                var componentLeft = (viewPortWidth - componentWidth) / 2;
                var componentTop = (viewPortHeight - componentHeight) / 2;
                calendarElement.style.width = componentWidth + 'px';
                calendarElement.style.position = 'fixed';
                calendarElement.style.left = componentLeft + 'px';
                calendarElement.style.top = componentTop + 'px';
            } else if (self.position == 'parent') {
                calendarElement.style.width = 'auto';
                calendarElement.style.position = 'relative';
                calendarElement.style.display = 'block';
            }
        }
    };
    this.hideCalendarElement = function() {
        if (!self.keepCalendarOpen) {
            self.state = 'closed';
            var calendarElement = self.getCalendarElement();
            calendarElement.style.display = 'none';
        } else {
            self.prepareCurrentValues();
            self.updateContents();
        }
    };
    this.getCalendarElement = function() {
        if (!this.calendarElement) {
            this.calendarElement = document.createElement('div');
            this.calendarElement.className = 'calendar_selector';

            if (self.parentElement) {
                self.parentElement.appendChild(this.calendarElement);
            } else {
                document.body.appendChild(this.calendarElement);
            }

            window.eventsManager.addHandler(this.calendarElement, 'click', this.calendarElementClick);

            var dateObject = new Date();
            dateObject.setHours(0, 0, 0);
            dateObject.setMilliseconds(0);

            todayDate = dateObject.getDate();
            todayMonth = dateObject.getMonth();
            todayYear = dateObject.getFullYear();
            todayStamp = dateObject.getTime();

            this.prepareDOMStructure();
        }
        return this.calendarElement;
    };
    this.prepareCurrentValues = function() {
        if (self.getActiveInputComponent()) {
            currentMonth = self.getActiveInputComponent().getMonth();
            currentYear = self.getActiveInputComponent().getYear();
        }
        if ((currentMonth == null) || (currentYear == null)) {
            var dateObject = new Date();

            currentMonth = dateObject.getMonth();
            currentYear = dateObject.getFullYear();
        }
    };
    this.showNextMonth = function() {
        var nextMonthFirstDay = new Date(currentYear, currentMonth + 1, 1);

        currentMonth = nextMonthFirstDay.getMonth();
        currentYear = nextMonthFirstDay.getFullYear();

        this.updateContents();
    };
    this.showPreviousMonth = function() {
        var previousMonthLastDay = new Date(currentYear, currentMonth, 0);

        currentMonth = previousMonthLastDay.getMonth();
        currentYear = previousMonthLastDay.getFullYear();

        this.updateContents();
    };
    this.prepareDOMStructure = function() {
        headerElement = document.createElement('div');
        headerElement.className = 'calendar_selector_header';
        this.headerElement = headerElement;
        this.calendarElement.appendChild(headerElement);

        var bodyElement = document.createElement('div');
        bodyElement.className = 'calendar_selector_body';
        this.bodyElement = bodyElement;
        this.calendarElement.appendChild(bodyElement);

        this.buttonNextMonth = new CalendarSelector_nextMonth(this);
        this.buttonPreviousMonth = new CalendarSelector_previousMonth(this);
        this.currentLocation = new CalendarSelector_currentLocation(this);

        this.closeButton = document.createElement('div');
        this.closeButton.className = 'calendar_selector_close';
        if (!self.keepCalendarOpen) {
            window.eventsManager.addHandler(this.closeButton, 'click', this.hideCalendarElement);
        }
        if (window.translationsLogics.get('desktop.calendar_close')) {
            this.closeButton.innerHTML = window.translationsLogics.get('desktop.calendar_close');
        }
        this.calendarElement.appendChild(this.closeButton);
    };
    this.selectDay = function(year, month, day) {
        day = this.formatNumber(day, 2);
        month = this.formatNumber(month + 1, 2);
        var dateText = day + '.' + month + '.' + year;

        // if use period select
        if (endComponent && startComponent) {

            // first click - select START
            if (!startComponent.getStamp() && !endComponent.getStamp()) {
                self.setActiveInputComponent(startComponent);
            }

            // second click - select END
            else if (startComponent.getStamp() && !endComponent.getStamp()) {
                self.setActiveInputComponent(endComponent);
            }

            // third click - CLEAR and select START
            else if (startComponent.getStamp() && endComponent.getStamp()) {
                startComponent.setValue('');
                endComponent.setValue('');
                self.setActiveInputComponent(startComponent);
            }
        }

        var activeInput = self.getActiveInputComponent();

        if (activeInput) {
            activeInput.setValue(dateText);
        }

        // if use period select
        if (endComponent && startComponent) {

            // if select second date before first, clear and start from this date
            if (activeInput == endComponent && startComponent.getStamp() > endComponent.getStamp()) {
                startComponent.setValue(dateText);
                endComponent.setValue('');
            }
        }

        if (!self.keepCalendarOpen) {
            this.hideCalendarElement();
        }

        if (self.changeCallBack) {
            self.changeCallBack(new Date(month + '/' + day + '/' + year));
        }
    };
    this.getActiveInputComponent = function() {
        if (!activeInputComponent) {
            if (inputComponent) {
                activeInputComponent = inputComponent;
            } else if (startComponent) {
                activeInputComponent = startComponent;
            }

        }
        return activeInputComponent;

    };
    this.setActiveInputComponent = function(inputComponent) {
        activeInputComponent = inputComponent;
    };
    this.formatNumber = function(number, decimals) {
        number = number.toString();
        if (number.length < decimals) {
            for (a = decimals - number.length; a > 0; a--) {
                number = '0' + number;
            }
        }
        return number;
    };
    this.updateContents = function() {
        var bodyElement = this.bodyElement;
        while (bodyElement.firstChild) {
            bodyElement.removeChild(bodyElement.firstChild);
        }
        if (daysElement) {
            self.calendarElement.removeChild(daysElement);
        }
        if (!this.monthPages[currentYear]) {
            this.monthPages[currentYear] = new Array();
        }
        var monthObject;
        if (!this.monthPages[currentYear][currentMonth]) {
            monthObject = new CalendarSelectorMonth(currentMonth, currentYear);
            this.monthPages[currentYear][currentMonth] = monthObject;
        } else {
            monthObject = this.monthPages[currentYear][currentMonth];
        }
        var tableElement = document.createElement('table');
        var tableBodyElement = document.createElement('tbody');

        tableElement.appendChild(tableBodyElement);

        if (self.showWeekDays) {
            daysElement = document.createElement('div');
            daysElement.className = 'calendar_selector_days';
            var tableElement2 = document.createElement('table');
            daysElement.appendChild(tableElement2);
            var rowElement = document.createElement('tr');
            tableElement2.appendChild(rowElement);

            var weekDaysNames = this.getWeekDaysNames();
            for (var i = 0; i < 7; ++i) {
                var cellElement = document.createElement('th');
                cellElement.innerHTML = weekDaysNames[i];
                rowElement.appendChild(cellElement);
            }
            self.calendarElement.insertBefore(daysElement, bodyElement);
        }

        for (var row = 0; row < monthObject.daysGrid.length; row++) {
            var tableRow = document.createElement('tr');

            for (var cell = 0, l = monthObject.daysGrid[row].length; cell !== l; cell++) {
                var month = monthObject.daysGrid[row][cell].getMonth();
                var day = monthObject.daysGrid[row][cell].getDate();
                var weekDay = monthObject.daysGrid[row][cell].getDay();
                var year = monthObject.daysGrid[row][cell].getFullYear();
                var stamp = monthObject.daysGrid[row][cell].getTime();
                var selectionDisabled = false;
                var active = false;
                var className = '';
                if (month == currentMonth && year == currentYear) {
                    className += ' calendar_selector_activemonth';
                    active = true;
                    selectionDisabled = false;
                }
                if (stamp < todayStamp && !active) {
                    className += ' calendar_selector_pastday';
                    selectionDisabled = true;
                }
                if (!active) {
                    className += ' calendar_selector_pastday';
                }
                if (weekDay == 0 || weekDay == 6) {
                    className += ' calendar_selector_weekend';
                }
                if (day == todayDate && month == todayMonth && year == todayYear) {
                    className += ' calendar_selector_today';
                }

                if (self.getActiveInputComponent()) {
                    if (day == self.getActiveInputComponent().getDate() && month == self.getActiveInputComponent().getMonth() && year == self.getActiveInputComponent().getYear()) {
                        className += ' calendar_selector_selected';
                    }
                }
                if (startComponent && endComponent) {
                    if (day == startComponent.getDate() && month == startComponent.getMonth() && year == startComponent.getYear()) {
                        className += ' calendar_selector_selected';
                    }
                    if (day == endComponent.getDate() && month == endComponent.getMonth() && year == endComponent.getYear()) {
                        className += ' calendar_selector_selected calendar_selector_selected_end';
                    }

                    if (stamp > startComponent.getStamp() && stamp < endComponent.getStamp()) {
                        className += ' calendar_selector_periodday';
                    }

                    if (self.persistPeriod && !selectionDisabled) {
                        if (stamp < startComponent.getStamp() && endComponent == activeInputComponent) {
                            selectionDisabled = true;
                        }
                        if (stamp > endComponent.getStamp() && startComponent == activeInputComponent && endComponent.getStamp()) {
                            selectionDisabled = true;
                        }
                    }
                }
                if (window.showCalendarLogics) {
                    var eventUrl = false;
                    var matchingConcert = showCalendarLogics.getConcertsByDate(day, month + 1, year);
                    if (matchingConcert) {
                        className += ' calendar_selector_has_event';
                        eventUrl = showCalendarLogics.getConcertUrl(matchingConcert.id);
                    }
                }

                var weekNr = false;
                if (self.showWeekNumbers && weekDay == 1) {
                    weekNr = self.getWeekNumber(year, month, day);
                }

                var dayObject = new CalendarSelectorDay(this, className, year, month, day, selectionDisabled, weekNr, eventUrl, self.hideHover);

                tableRow.appendChild(dayObject.domElement);
            }
            tableBodyElement.appendChild(tableRow);
        }
        bodyElement.appendChild(tableElement);

        var monthsNames = this.getMonthsNames();
        this.currentLocation.setLocation(monthsNames[currentMonth], currentYear);
    };
    var y2k = function(number) {
        return (number < 1000) ? number + 1900 : number;
    };
    this.getWeekNumber = function(year, month, day) {
        var when = new Date(year, month, day);
        var newYear = new Date(year, 0, 1);
        var modDay = newYear.getDay();
        var weeknr = false;
        if (modDay == 0) {
            modDay = 6;
        } else {
            modDay--;
        }

        var daynum = ((Date.UTC(y2k(year), when.getMonth(), when.getDate(), 0, 0, 0) - Date.UTC(y2k(year), 0, 1, 0, 0, 0)) / 1000 / 60 / 60 / 24) + 1;

        if (modDay < 4) {
            weeknr = Math.floor((daynum + modDay - 1) / 7) + 1;
        } else {
            weeknr = Math.floor((daynum + modDay - 1) / 7);
            if (weeknr == 0) {
                year--;
                var prevNewYear = new Date(year, 0, 1);
                var prevmodDay = prevNewYear.getDay();
                if (prevmodDay == 0) {
                    prevmodDay = 6;
                } else {
                    prevmodDay--;
                }
                if (prevmodDay < 4) {
                    weeknr = 53;
                } else {
                    weeknr = 52;
                }
            }
        }

        return +weeknr;
    };
    this.getWeekDaysNames = function() {
        var weekDaysNames = [];
        if (window.translationsLogics) {

            for (var i = 0; i < 7; i++) {
                weekDaysNames[i] = window.translationsLogics.get('weekdays_short.' + i);
            }
        } else {
            weekDaysNames[0] = 'E';
            weekDaysNames[1] = 'T';
            weekDaysNames[2] = 'K';
            weekDaysNames[3] = 'N';
            weekDaysNames[4] = 'R';
            weekDaysNames[5] = 'L';
            weekDaysNames[6] = 'P';
        }
        return weekDaysNames;
    };
    this.getMonthsNames = function() {
        var monthsNames = {};
        if (window.translations) {
            for (var i = 0; i < 12; i++) {
                monthsNames[i] = window.translationsLogics.get('calendar.month_' + (i + 1));
            }
        } else {
            monthsNames = new Array();
            monthsNames[0] = 'Jaanuar';
            monthsNames[1] = 'Veebruar';
            monthsNames[2] = 'Märts';
            monthsNames[2] = 'Märts';
            monthsNames[3] = 'Aprill';
            monthsNames[4] = 'Mai';
            monthsNames[5] = 'Juuni';
            monthsNames[6] = 'Juuli';
            monthsNames[7] = 'August';
            monthsNames[8] = 'September';
            monthsNames[9] = 'Oktoober';
            monthsNames[10] = 'November';
            monthsNames[11] = 'Detsember';
        }
        return monthsNames;
    };
    this.getStartComponent = function() {
        return startComponent;
    };
    this.getEndComponent = function() {
        return endComponent;
    };
    this.init();
};
window.CalendarSelectorInput = function(inputElement, calendarObject) {
    var self = this;
    var date;
    var month;
    var year;
    var stamp;
    this.inputElement = false;

    var init = function() {
        self.inputElement = inputElement;
        if (typeof inputElement.value !== 'undefined') {
            window.eventsManager.addHandler(inputElement, 'focus', displayCalendarElement);
            window.eventsManager.addHandler(inputElement, 'click', inputElementClick);
            window.eventsManager.addHandler(inputElement, 'change', changeHandler);

            if (calendarObject.disableInput) {
                inputElement.setAttribute('readonly', 'readonly');
            }
        } else {
            window.eventsManager.addHandler(inputElement, 'click', displayCalendarElement);
        }
    };
    var displayCalendarElement = function() {
        calendarObject.setActiveInputComponent(self);
        calendarObject.displayCalendarElement();
    };
    var changeHandler = function() {
        date = null;
        month = null;
        year = null;
        stamp = null;
        calendarObject.hideCalendarElement();
        domHelper.removeClass(inputElement, calendarObject.activeInputClassName);
    };

    var inputElementClick = function(event) {
        if (calendarObject.activeInputClassName) {
            domHelper.addClass(inputElement, calendarObject.activeInputClassName);
        }
        window.eventsManager.cancelBubbling(event);
    };
    this.getElementPositions = function() {
        return domHelper.getElementPositions(inputElement);
    };
    this.getInputHeight = function() {
        return inputElement.offsetHeight;
    };
    this.getValue = function() {
        if (inputElement.value) {
            return inputElement.value;
        } else {
            return inputElement.innerHTML;
        }
    };
    this.setValue = function(value) {
        date = null;
        month = null;
        year = null;
        stamp = null;
        if (typeof inputElement.value !== 'undefined') {
            inputElement.value = value;
            window.eventsManager.fireEvent(inputElement, 'change');
        } else {
            inputElement.innerHTML = value;
        }
    };
    this.getDate = function() {
        if (!date) {
            parseValue();
        }
        return date;
    };
    this.getMonth = function() {
        if (!month) {
            parseValue();
        }
        return month;
    };
    this.getYear = function() {
        if (!year) {
            parseValue();
        }
        return year;
    };
    this.getStamp = function() {
        if (!stamp) {
            parseValue();
        }
        return stamp;
    };
    var parseValue = function() {
        var textValue = self.getValue();
        if (textValue.match(/^(0[1-9]|[12][0-9]|3[01])[- \/.](0[1-9]|1[012])[- \/.](19|20)\d\d$/)) {
            var dateParts = textValue.split('.');
            date = parseInt(dateParts[0], 10);
            month = parseInt(dateParts[1], 10) - 1;
            year = parseInt(dateParts[2], 10);

            var dateObject = new Date(year, month, date);
            stamp = dateObject.getTime();
        }
    };

    init();
};
window.CalendarSelectorDay = function(calendarObject, className, year, month, day, selectionDisabled, weekNr, eventLink, hideHover) {
    this.domElement = false;
    this.day = false;
    this.month = false;
    this.year = false;
    var self = this;

    this.init = function() {
        this.calendarObject = calendarObject;
        this.className = className;
        this.month = month;
        this.year = year;
        this.day = day;
        this.createDomElement();
    };
    this.createDomElement = function() {
        var domElement = document.createElement('td');
        this.domElement = domElement;
        this.domElement.className = this.className;
        if (weekNr) {
            var weekNrElement = document.createElement('span');
            weekNrElement.className = 'calendar_week_nr';
            weekNrElement.innerText = weekNr;
            domElement.appendChild(weekNrElement);
        }
        var content = document.createTextNode(this.day);
        var wrapperContent = document.createElement('span');

        wrapperContent.appendChild(content);

        domElement.appendChild(wrapperContent);

        if (!selectionDisabled) {
            window.eventsManager.addHandler(domElement, 'click', this.clickHandler);
            if (!hideHover) {
                window.eventsManager.addHandler(domElement, 'mouseover', this.mouseOverHandler);
                window.eventsManager.addHandler(domElement, 'mouseout', this.mouseOutHandler);
            }
        }
    };
    this.mouseOverHandler = function() {
        self.domElement.className = self.className + ' calendar_selector_hovered';
    };
    this.mouseOutHandler = function() {
        self.domElement.className = self.className;
    };
    this.clickHandler = function(event) {
        if (eventLink) {
            window.location.href = eventLink;
        } else {
            eventsManager.cancelBubbling(event);
            self.calendarObject.selectDay(self.year, self.month, self.day);
        }
    };
    this.init();
};
window.CalendarSelectorMonth = function(month, year) {
    this.init = function() {
        this.month = month;
        this.year = year;

        var monthFirstDay = new Date(year, month, 1);

        var weekDay = monthFirstDay.getDay() - 1;
        if (weekDay < 0) {
            weekDay = 6;
        }

        var tableFirstDay = new Date(year, month, 1 - weekDay);

        var monthLastDay = new Date(year, month + 1, 0);

        weekDay = monthLastDay.getDay() - 1;
        if (weekDay < 0) {
            weekDay = 6;
        }

        var tableLastDay = new Date(year, month + 1, 6 - weekDay);

        this.monthFirstDay = monthFirstDay;
        this.monthLastDay = monthLastDay;
        this.tableFirstDay = tableFirstDay;
        this.tableLastDay = tableLastDay;

        this.calculateDaysData();
    };
    this.calculateDaysData = function() {
        var todayDate = new Date();
        todayDate.setTime(this.tableFirstDay.getTime());

        var currentRow = 0;
        var currentWeekDay = 0;

        while (todayDate.getTime() <= this.tableLastDay.getTime()) {
            if (!this.daysGrid[currentRow]) {
                this.daysGrid[currentRow] = new Array();
            }

            this.daysGrid[currentRow][currentWeekDay] = new Date(todayDate.getTime());

            todayDate.setDate(todayDate.getDate() + 1);

            currentWeekDay++;
            if (currentWeekDay == 7) {
                currentWeekDay = 0;
                currentRow++;
            }
        }
    };
    this.daysGrid = new Array();
    this.init();
};
window.CalendarSelector_nextMonth = function(calendarObject) {
    this.init = function() {
        this.calendarObject = calendarObject;
        var buttonElement = document.createElement('span');
        buttonElement.className = 'calendar_selector_nextmonth';
        calendarObject.headerElement.appendChild(buttonElement);

        var buttonText = '';

        if (buttonElement.textContent != null) {
            buttonElement.textContent = buttonText;
        } else {
            buttonElement.innerText = buttonText;
        }

        window.eventsManager.addHandler(buttonElement, 'click', this.click);
    };
    this.click = function(event) {
        window.eventsManager.preventDefaultAction(event);
        self.calendarObject.showNextMonth();
    };
    var self = this;
    this.init();
};
window.CalendarSelector_previousMonth = function(calendarObject) {
    this.init = function() {
        this.calendarObject = calendarObject;
        var buttonElement = document.createElement('span');
        buttonElement.className = 'calendar_selector_previousmonth';
        calendarObject.headerElement.appendChild(buttonElement);
        buttonElement.href = '';

        var buttonText = '';

        if (buttonElement.textContent != null) {
            buttonElement.textContent = buttonText;
        } else {
            buttonElement.innerText = buttonText;
        }

        window.eventsManager.addHandler(buttonElement, 'click', this.click);
    };
    this.click = function(event) {
        window.eventsManager.preventDefaultAction(event);
        self.calendarObject.showPreviousMonth();
    };
    var self = this;
    this.init();
};
window.CalendarSelector_currentLocation = function(calendarObject) {
    this.init = function() {
        this.calendarObject = calendarObject;
        var locationElement = document.createElement('div');
        locationElement.className = 'calendar_selector_location';
        calendarObject.headerElement.appendChild(locationElement);

        this.locationElement = locationElement;
    };
    this.setLocation = function(month, year) {
        var locationText = month + ' ' + year;

        if (this.locationElement.textContent != null) {
            this.locationElement.textContent = locationText;
        } else {
            this.locationElement.innerText = locationText;
        }
    };
    this.init();
};