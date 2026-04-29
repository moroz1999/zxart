window.AddNewElementComponent = function(componentElement) {
    var self = this;
    var info;
    var buttonId;
    var popupComponent;
    var singleElementInfo;
    var state = 'closed';

    this.componentElement = false;

    var init = function() {
        var labelElement;
        self.componentElement = componentElement;
        buttonId = componentElement.id;
        if (info = newElementsLogics.getButtonInfo(buttonId)) {
            if (info.length > 1) {
                popupComponent = new AddNewElementPopupComponent(info, self);
                if (labelElement = componentElement.querySelector('.addnew_label')) {
                    labelElement.innerHTML += '...';
                }
            } else {
                singleElementInfo = info[0];

                var containerElement = document.createElement('div');
                containerElement.className = 'addnew_container';
                containerElement.style = 'position: relative;';

                componentElement.appendChild(containerElement);

                var iconElement = document.createElement('span');
                iconElement.className = singleElementInfo.icon;

                containerElement.appendChild(iconElement);

                var textElement = document.createElement('span');
                textElement.className = 'addnewelement_text';
                textElement.innerHTML = singleElementInfo.name;

                containerElement.appendChild(textElement);
            }

            eventsManager.addHandler(componentElement, 'click', clickHandler);
        }
    };
    var clickHandler = function(event) {
        eventsManager.cancelBubbling(event);
        if (popupComponent) {
            if (state === 'closed') {
                state = 'opened';
                domHelper.addClass(componentElement, 'button_blue');
                popupComponent.displayComponent();
            } else {
                state = 'closed';
                domHelper.removeClass(componentElement, 'button_blue');
                popupComponent.hideComponent();
            }
        } else if (singleElementInfo) {
            document.location.href = singleElementInfo.url;
        }
    };
    this.closePopup = function() {
        if (popupComponent) {
            state = 'closed';
            domHelper.removeClass(componentElement, 'button_blue');
            popupComponent.hideComponent();
        }
    };

    init();
};
window.AddNewElementPopupComponent = function(buttonsList, parentComponent) {
    var self = this;
    var componentElement;
    var itemsElement;
    var elementsInRow = 3;

    var init = function() {
        if (buttonsList.length) {
            buttonsList.sort(function(a, b) {
                if (a.name < b.name) {
                    return -1;
                }
                if (a.name > b.name) {
                    return 1;
                }
                return 0;
            });
            componentElement = document.createElement('div');
            componentElement.className = 'addnewelement_popup';

            itemsElement = document.createElement('div');
            itemsElement.className = 'addnewelement_popup_items';

            if (buttonsList.length > 10) {
                itemsElement.style.columnCount = 3;
            } else if (buttonsList.length > 5) {
                itemsElement.style.columnCount = 2;
            }

            for (var i = 0; i < buttonsList.length; i++) {
                var itemElement = document.createElement('div');
                itemElement.className = 'addnewelement_popup_item';

                var button = new AddNewElementPopupButtonComponent(buttonsList[i]);
                itemElement.appendChild(button.componentElement);
                itemsElement.appendChild(itemElement);
            }

            componentElement.appendChild(itemsElement);
            document.body.appendChild(componentElement);
            eventsManager.addHandler(componentElement, 'click', clickHandler);
        }
    };
    this.displayComponent = function() {
        componentElement.style.visibility = 'hidden';
        componentElement.style.display = 'block';

        adjustPositions();
        componentElement.style.visibility = 'visible';
    };
    this.hideComponent = function() {
        componentElement.style.visibility = 'hidden';
    };
    var clickHandler = function(event) {
        eventsManager.cancelBubbling(event);
    };
    var adjustPositions = function() {
        var positions = domHelper.getElementPositions(parentComponent.componentElement);
        componentElement.style.left = positions.x + 'px';
        componentElement.style.top = (parentComponent.componentElement.offsetHeight + positions.y + 10) + 'px';
    };
    init();
};
window.AddNewElementPopupButtonComponent = function(info) {
    var self = this;
    var componentElement;

    this.componentElement = false;

    var init = function() {
        componentElement = document.createElement('a');
        componentElement.className = 'structure_element addnewelement_button';
        componentElement.href = info.url;

        var iconElement = document.createElement('span');
        iconElement.className = info.icon;

        componentElement.appendChild(iconElement);

        var textElement = document.createElement('span');
        textElement.innerHTML = info.name;

        componentElement.appendChild(textElement);

        self.componentElement = componentElement;
    };
    init();
};