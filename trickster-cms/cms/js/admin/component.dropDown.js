window.DropDownComponent = function(importedElement, parameters) {
    var init = function() {
        if (typeof parameters !== 'undefined') {
            parseParameters(parameters);
        }
        if (typeof importedElement == 'object') {
            parseSelectElement(importedElement);
        }
        prepareDomStructure();

        refreshStatus();
    };
    var parseSelectElement = function(importedElement) {
        optionsDataList = [];
        if (importedElement.tagName == 'select' || importedElement.tagName == 'SELECT') {
            self.selectorElement = importedElement;
            for (var i = 0; i < importedElement.options.length; i++) {
                var optionElement = importedElement.options[i];

                var optionData = {};
                optionData['value'] = optionElement.value;
                optionData['style'] = optionElement.getAttribute('style');
                optionData['className'] = optionElement.className;
                optionData['text'] = optionElement.innerHTML;
                optionData['selected'] = optionElement.selected;

                optionsDataList.push(optionData);
            }
            importedElement.style.display = 'none';
        }

        customClassName = '';
        var classes = importedElement.className.split(' ');
        for (var i = 0; i < classes.length; i++) {
            if (classes[i] != 'dropdown_placeholder') {
                customClassName = customClassName + ' ' + classes[i];
            }
        }
    };
    var parseParameters = function(parameters) {
        if (typeof parameters.optionsData !== 'undefined') {
            optionsDataList = parameters.optionsData;
            for (var i = 0; i < optionsDataList.length; i++) {
                var optionData = optionsDataList[i];
            }
        }
        if (typeof parameters.changeCallback !== 'undefined') {
            changeCallback = parameters.changeCallback;
        }
        if (typeof parameters.className !== 'undefined') {
            customClassName = parameters.className;
        }
        if (typeof parameters.name !== 'undefined') {
            selectName = parameters.name;
        }
    };
    var prepareDomStructure = function() {
        var componentClass = 'dropdown_block';

        if (customClassName != '') {
            componentClass += ' ' + customClassName;
        }

        self.componentElement = document.createElement('a');
        self.componentElement.href = '';
        self.componentElement.className = componentClass;

        window.eventsManager.addHandler(self.componentElement, 'click', clickHandler);
        window.eventsManager.addHandler(self.componentElement, 'focus', clearSearchTitle);
        window.eventsManager.addHandler(self.componentElement, 'keydown', keyPressHandler);

        titleElement = document.createElement('span');
        titleElement.className = 'dropdown_title';
        self.componentElement.appendChild(titleElement);

        if (!self.selectorElement) {
            self.selectorElement = document.createElement('select');
            if (selectName) {
                self.selectorElement.name = selectName;
            }

            fillSelectorElement();
            self.componentElement.appendChild(self.selectorElement);
        }
        self.selectorElement.style.display = 'none';

        if (!optionsDataComponent) {
            optionsDataComponent = new DropDownComponentList(self, optionsDataList);
            document.body.appendChild(optionsDataComponent.componentElement);
        }
        window.eventsManager.addHandler(self.selectorElement, 'change', changeHandler);
    };
    var fillSelectorElement = function() {
        for (var i = 0; i < optionsDataList.length; i++) {
            var info = optionsDataList[i];

            var option = document.createElement('option');
            option.value = info.value;
            if (typeof info.listText !== 'undefined') {
                option.text = info.listText;
            } else {
                option.text = info.text;
            }
            if (typeof info.color !== 'undefined') {
                option.color = info.color;
            }
            if (typeof info.className !== 'undefined') {
                option.className = info.className;
            }
            option.selected = info.selected;
            try {
                self.selectorElement.add(option, null);
            } catch (ex) {
                self.selectorElement.add(option);
            }
        }
    };
    var changeHandler = function() {
        refreshStatus();
        if (changeCallback) {
            changeCallback(self);
        }
    };
    var clickHandler = function(event) {
        window.eventsManager.preventDefaultAction(event);
        window.eventsManager.cancelBubbling(event);

        if (optionsDataComponent.displayed) {
            self.hideList();
        } else {
            window.dropDownManager.hideLists();
            optionsDataComponent.displayComponent();
            self.componentElement.focus(); //fix for chrome losing focus
            domHelper.addClass(self.componentElement, 'dropdown_focused');
        }
    };
    var refreshStatus = function() {
        self.selectedIndex = self.selectorElement.selectedIndex;
        self.text = '';
        self.value = self.selectorElement.value;

        if (optionsDataList[self.selectedIndex]) {
            if (typeof optionsDataList[self.selectedIndex].displayText !== 'undefined') {
                self.text = optionsDataList[self.selectedIndex].displayText;
            } else {
                self.text = optionsDataList[self.selectedIndex].text;
            }
        }

        titleElement.innerHTML = self.text;

        if (optionsDataComponent) {
            optionsDataComponent.updateScroll(self.selectedIndex);
        }
    };
    var keyPressHandler = function(event) {
        if (event.keyCode == '40') {
            window.eventsManager.preventDefaultAction(event);
            setNextOption();
        }
        if (event.keyCode == '38') {
            window.eventsManager.preventDefaultAction(event);
            setPreviousOption();
        }
        if (event.keyCode == '35') {
            window.eventsManager.preventDefaultAction(event);
            setLastOption();
        }
        if (event.keyCode == '36') {
            window.eventsManager.preventDefaultAction(event);
            setFirstOption();
        }
        if (event.keyCode == '8') {
            window.eventsManager.preventDefaultAction(event);
            var title = getCurrentSearchTitle(false);
            setFoundTitle(title);
        }
        if (event.keyCode >= '65' && event.keyCode <= '90' || event.keyCode == '32' || event.keyCode >= '48' && event.keyCode <= '57') {
            window.eventsManager.preventDefaultAction(event);
            var letter = String.fromCharCode(event.keyCode);
            var title = getCurrentSearchTitle(letter);
            setFoundTitle(title);
        }
    };
    var getCurrentSearchTitle = function(letter) {
        if (letter === false) {
            currentSearchTitle = currentSearchTitle.substring(0, currentSearchTitle.length - 1);
        } else if (typeof letter !== 'undefined') {
            currentSearchTitle = currentSearchTitle + letter;
        }
        return currentSearchTitle;
    };
    var clearSearchTitle = function() {
        currentSearchTitle = '';
    };
    var setFoundTitle = function(title) {
        var expression = new RegExp('^(\\s)*' + title, 'i');

        for (var i = 0; i < optionsDataList.length; i++) {
            if (expression.test(optionsDataList[i].text)) {
                self.setValue(optionsDataList[i].value);
                break;
            }
        }
        window.clearTimeout(searchTitleTimeout);
        searchTitleTimeout = window.setTimeout(clearSearchTitle, 1500);
    };
    var setFirstOption = function() {
        if (optionsDataList.length > 0) {
            self.setSelectedIndex(0);
        }
        clearSearchTitle();
    };
    var setLastOption = function() {
        if (optionsDataList.length > 0) {
            self.setSelectedIndex(optionsDataList.length - 1);
        }
        clearSearchTitle();
    };
    var setNextOption = function() {
        if (self.selectedIndex !== false) {
            var nextOptionNumber = self.selectedIndex + 1;
            if (nextOptionNumber < optionsDataList.length) {
                self.setSelectedIndex(nextOptionNumber);
                clearSearchTitle();
            }
        } else {
            setFirstOption();
        }
    };
    var setPreviousOption = function() {
        if (self.selectedIndex !== false) {
            var previousOptionNumber = self.selectedIndex - 1;
            if (previousOptionNumber >= 0) {
                self.setSelectedIndex(previousOptionNumber);
                clearSearchTitle();
            }
        }
    };

    this.getComponentElement = function() {
        return self.componentElement;
    };
    this.setSelectedIndex = function(selectedIndex) {
        self.selectorElement.selectedIndex = selectedIndex;
        window.eventsManager.fireEvent(self.selectorElement, 'change');

        if (changeCallback) {
            changeCallback(self);
        }
    };
    this.getValue = function() {
        var value = self.selectorElement.value;
        if (value == 'defaultTypePlaceHolder') {
            value = '';
        }
        return value;
    };
    this.setDisabled = function(value) {
        self.disabled = value;
        self.selectorElement.disabled = value;
        refreshStatus();
    };
    this.setValue = function(value, ignoreChangeEvent) {
        self.selectorElement.value = value;
        if (ignoreChangeEvent) {
            refreshStatus();
        } else {
            window.eventsManager.fireEvent(self.selectorElement, 'change');
        }
    };
    this.hideList = function() {
        if (optionsDataComponent.displayed) {
            domHelper.removeClass(self.componentElement, 'dropdown_focused');
            optionsDataComponent.hideComponent();
        }
    };
    this.update = function() {
        parseSelectElement(self.selectorElement);
        if (optionsDataComponent) {
            optionsDataComponent.updateInfo(optionsDataList);
        }
        refreshStatus();
    };
    this.updateOptionsData = function(optionsData, callCallback) {
        if (typeof callCallback == 'undefined') {
            callCallback = false;
        }
        while (self.selectorElement.firstChild) {
            self.selectorElement.removeChild(self.selectorElement.firstChild);
        }
        optionsDataList = optionsData;
        fillSelectorElement();

        optionsDataComponent.updateInfo(optionsDataList);

        refreshStatus();
        if (callCallback && changeCallback) {
            changeCallback(self);
        }
    };
    this.displayComponent = function() {
        self.componentElement.style.display = 'block';
    };
    this.hideComponent = function() {
        self.componentElement.style.display = 'none';
    };
    this.setChangeCallback = function(callback) {
        changeCallback = callback;
    };
    var self = this;

    //public properties
    this.componentElement = null;
    this.selectedIndex = false;
    this.value = '';
    this.text = '';
    this.disabled = false;
    this.selectorElement = null;

    //dom structure
    var titleElement;
    var optionsDataComponent;

    //private properties
    var searchTitleTimeout = false;
    var currentSearchTitle = false;
    var changeCallback = false;
    var optionsDataList = [];
    var selectName = '';

    var customClassName = '';

    init();
};
window.DropDownComponentList = function(parentObject, initOptionsData) {
    var init = function() {
        prepareDomStructure();
    };
    var prepareDomStructure = function() {
        self.componentElement = document.createElement('div');
        self.componentElement.className = 'dropdown_list';
        self.componentElement.style.display = 'none';
        window.eventsManager.addHandler(self.componentElement, 'mousewheel', mouseWheelHandler);
        window.eventsManager.addHandler(window, 'resize', refreshStatus);

        contentElement = document.createElement('span');
        contentElement.className = 'dropdown_list_content';
        self.componentElement.appendChild(contentElement);

        self.updateInfo(initOptionsData);
    };
    var mouseWheelHandler = function(event) {
        window.eventsManager.preventDefaultAction(event);
        var delta = window.mouseTracker.getDelta(event);

        contentElement.scrollTop = contentElement.scrollTop - listItemHeight * delta;
    };
    var refreshStatus = function() {
        self.componentElement.style.width = parentObject.componentElement.offsetWidth - 2 + 'px';

        if (listItems.length > 0) {
            listItemHeight = listItems[0].componentElement.offsetHeight;
        }

        if (self.componentElement.offsetWidth < self.componentElement.scrollWidth) {
            self.componentElement.style.width = parentObject.componentElement.scrollWidth - 2 + 'px';
        }

        if (window.pageYOffset) {
            var viewPortTop = window.pageYOffset;
        } else {
            var viewPortTop = document.documentElement.scrollTop;
        }

        if (window.innerHeight) {
            var viewPortHeight = window.innerHeight;
        } else {
            var viewPortHeight = document.documentElement.offsetHeight;
        }
        var dropDownPositions = getElementPositions(parentObject.componentElement);
        var dropDownLeft = dropDownPositions.x;
        var dropDownTop = dropDownPositions.y;
        var dropDownHeight = parentObject.componentElement.offsetHeight;

        //calculate possible list heights
        contentElement.style.height = 'auto';
        var fullHeight = contentElement.offsetHeight;

        var maximumHeightAbove = (dropDownTop - viewPortTop) - screenOffset;
        var maximumHeightBelow = (viewPortTop + viewPortHeight - screenOffset) - (dropDownTop + dropDownHeight);

        var appliedHeight = false;
        var position = false;
        if (maximumHeightBelow > maximumHeightAbove || fullHeight < maximumHeightBelow) {
            position = 'below';
            if (fullHeight > maximumHeightBelow) {
                appliedHeight = maximumHeightBelow;
            } else {
                appliedHeight = fullHeight;
            }
        } else {
            position = 'above';
            if (fullHeight > maximumHeightAbove) {
                appliedHeight = maximumHeightAbove;
            } else {
                appliedHeight = fullHeight;
            }
        }
        contentElement.style.height = appliedHeight + 'px';

        //calculate list position
        if (position == 'above') {
            var leftPosition = (dropDownLeft);
            var topPosition = dropDownTop - appliedHeight;
            domHelper.addClass(self.componentElement, 'dropdown_list_is_above');
        } else {
            var leftPosition = (dropDownLeft);
            var topPosition = (dropDownTop + dropDownHeight) - 2;
            domHelper.removeClass(self.componentElement, 'dropdown_list_is_above');
        }

        self.componentElement.style.left = leftPosition + 'px';
        self.componentElement.style.top = topPosition + 'px';

        if (contentElement.scrollHeight > contentElement.offsetHeight) {
            if (contentElement.offsetHeight + contentElement.scrollTop < parentObject.selectedIndex * listItemHeight + listItemHeight) {
                contentElement.scrollTop = parentObject.selectedIndex * listItemHeight + listItemHeight - contentElement.offsetHeight;
            } else if (contentElement.scrollTop > parentObject.selectedIndex * listItemHeight) {
                contentElement.scrollTop = parentObject.selectedIndex * listItemHeight;
            }
        }
    };
    this.updateScroll = function(selectedIndex) {
        if (typeof listItems[selectedIndex] !== 'undefined') {
            contentElement.scrollTop = listItems[selectedIndex].componentElement.offsetTop;
        }
    };
    var getElementPositions = function(domElement) {
        var elementLeft = 0;
        var elementTop = 0;

        if (domElement.offsetParent) {
            elementLeft = domElement.offsetLeft;
            elementTop = domElement.offsetTop;
            while (domElement = domElement.offsetParent) {
                if (domElement.tagName.toLowerCase() != 'body' && domElement.tagName.toLowerCase() != 'html') {
                    elementLeft += domElement.offsetLeft - domElement.scrollLeft;
                    elementTop += domElement.offsetTop - domElement.scrollTop;
                } else {
                    elementLeft += domElement.offsetLeft;
                    elementTop += domElement.offsetTop;
                }
            }
        }
        return {x: elementLeft, y: elementTop};
    };
    this.updateInfo = function(updateOptionsData) {
        optionsData = updateOptionsData;
        listItems = [];
        while (contentElement.firstChild) {
            contentElement.removeChild(contentElement.firstChild);
        }
        for (var i = 0; i < optionsData.length; i++) {
            var listItem = new DropDownComponentListItem(self, optionsData[i]);
            contentElement.appendChild(listItem.componentElement);
            listItems.push(listItem);
        }
    };
    this.itemClicked = function(listItem) {
        self.hideComponent();
        parentObject.setValue(listItem.value);
    };
    this.displayComponent = function() {
        if (self.componentElement) {
            self.componentElement.style.visibility = 'hidden';
            self.componentElement.style.display = 'block';
            refreshStatus();
            self.componentElement.style.visibility = 'visible';
            self.displayed = true;
        }
    };
    this.hideComponent = function() {
        if (self.componentElement) {
            self.componentElement.style.display = 'none';
            self.displayed = false;
        }
    };
    var self = this;
    var listItems = [];

    var screenOffset = 30;
    var listItemHeight = 0;
    var contentElement = false;

    var optionsData = false;

    //public properties
    this.componentElement = false;
    this.value = false;
    this.displayed = false;

    init();
};
window.DropDownComponentListItem = function(parentObject, optionData) {
    var init = function() {
        self.value = optionData.value;
        if (typeof optionData.text !== 'undefined') {
            optionText = optionData.text;
        }
        if (typeof optionData.className !== 'undefined') {
            customClassName = optionData.className;
        }
        if (typeof optionData.style !== 'undefined') {
            customStyle = optionData.style;
        }

        prepareDomStructure();
    };
    var prepareDomStructure = function() {
        self.componentElement = document.createElement('span');
        self.componentElement.tabIndex = 0;

        if (customStyle) {
            self.componentElement.style = customStyle;
        }

        var newClassName = 'dropdown_option';
        if (customClassName) {
            newClassName = newClassName + ' ' + customClassName;
        }
        self.componentElement.className = newClassName;

        window.eventsManager.addHandler(self.componentElement, 'click', clickHandler);

        self.componentElement.innerHTML = optionText;
    };
    var clickHandler = function(event) {
        window.eventsManager.preventDefaultAction(event);
        window.eventsManager.cancelBubbling(event);
        parentObject.itemClicked(self);
    };
    var self = this;

    this.componentElement = false;

    this.value = false;

    var customStyle = false;
    var optionText = false;
    var customClassName = false;

    init();
};