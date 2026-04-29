window.ToolTipComponent = function(parameters, popupText_deprecated, excludedElements_deprecated, classNameExtra_deprecated) {
    let self = this;
    let referralElement;
    let componentElement;
    let contentElement;
    let popupText;
    let popupOffset = 12;
    let displayDelay = 100;
    let displayed = false;
    let displayAllowed;
    let displayTimeout;
    let hideOnClick;
    let hideOnLeave;
    let fixedX = false;
    let fixedY = false;
    let excludedElements;
    let classNameExtra;
    let behaviourType;
    let beforeDisplay;
    let behaviourTypeDefault = 'mouseover';
    let elementsCreated = false;
    let attached = false;
    let popupPointerStyle;
    let classNameReferralSelected;

    const init = function() {
        //backward compatibility with old arguments, comes first
        if (typeof parameters == 'object') {
            parseParameters(parameters);
        } else {
            referralElement = parameters;
        }
        if (popupText_deprecated) {
            popupText = popupText_deprecated;
        }
        if (excludedElements_deprecated) {
            excludedElements = excludedElements_deprecated;
        }
        if (classNameExtra_deprecated) {
            classNameExtra = classNameExtra_deprecated;
        }

        addMouseHandlers();
    };
    const parseParameters = function(parameters) {
        if (typeof parameters.referralElement !== 'undefined') {
            referralElement = parameters.referralElement;
        }
        if (typeof parameters.popupText !== 'undefined') {
            popupText = parameters.popupText;
        }
        if (typeof parameters.classNameExtra !== 'undefined') {
            classNameExtra = parameters.classNameExtra;
        }
        if (typeof parameters.excludedElements !== 'undefined') {
            excludedElements = parameters.excludedElements;
        }
        if (typeof parameters.excludedElements !== 'undefined') {
            excludedElements = parameters.excludedElements;
        }
        if (typeof parameters.behaviourType !== 'undefined') {
            behaviourType = parameters.behaviourType;
        } else {
            behaviourType = behaviourTypeDefault;
        }
        if (typeof parameters.hintPointer !== 'undefined') {
            hintPointer = parameters.hintPointer;
        }
        if (typeof parameters.classNameReferralSelected !== 'undefined') {
            classNameReferralSelected = parameters.classNameReferralSelected;
        }
        if (typeof parameters.fixedX !== 'undefined') {
            fixedX = parameters.fixedX;
        }
        if (typeof parameters.hideOnClick !== 'undefined') {
            hideOnClick = parameters.hideOnClick;
        } else {
            hideOnClick = true;
        }
        if (typeof parameters.beforeDisplay !== 'undefined') {
            beforeDisplay = parameters.beforeDisplay;
        } else {
            beforeDisplay = true;
        }
        if (typeof parameters.hideOnLeave !== 'undefined') {
            hideOnLeave = parameters.hideOnLeave;
        } else {
            hideOnLeave = true;
        }
    };
    const createDomElements = function() {
        elementsCreated = true;
        componentElement = document.createElement('div');
        componentElement.className = 'tip_popup';
        componentElement.style['pointerEvents'] = 'none';
        if (classNameExtra) {
            componentElement.className += ' ' + classNameExtra;
        }
        componentElement.style.display = 'none';

        contentElement = document.createElement('div');
        contentElement.className = 'tip_popup_content';
        componentElement.appendChild(contentElement);

        contentElement.innerHTML = popupText;
    };
    const attach = function() {
        if (!attached) {
            if (!elementsCreated) {
                createDomElements();
            }
            attached = true;
            document.body.appendChild(componentElement);
        }
    };
    const detach = function() {
        if (attached) {
            attached = false;
            document.body.removeChild(componentElement);
        }
    };
    const moveHandler = function() {
        updatePosition();
    };
    const resizeHandler = function() {
        if (behaviourType === 'mouseover' || behaviourType === 'click') {
            // handle zoom changes
            updatePosition();
        }
    };
    const enterHandler = function() {
        if (popupText && displayAllowed && !displayed) {
            displayTimeout = window.setTimeout(self.displayComponent, displayDelay);
        }
    };
    const overHandler = function(event) {
        displayAllowed = checkExcluded(event.target);
        if (beforeDisplay) {
            displayAllowed &= beforeDisplay();
        }
        if (popupText && (displayAllowed)) {
            if (!displayed) {
                self.displayComponent();
            }
        } else if (displayed) {
            self.hideComponent();
        }
    };
    const clickHandler = function(event) {
        displayAllowed = checkExcluded(event.target);
        if (popupText && (displayAllowed)) {
            if (!displayed) {
                self.displayComponent();
            } else if (displayed && hideOnClick) {
                self.hideComponent();
            }
        }
    };
    const checkExcluded = function(element) {
        let result = true;
        if (excludedElements) {
            for (let i = 0; i < excludedElements.length; i++) {
                if ((excludedElements[i] === element) || isAChildOf(excludedElements[i], element)) {
                    result = false;
                    break;
                }
            }
        }
        return result;
    };
    const leaveHandler = function() {
        window.clearTimeout(displayTimeout);
        TweenLite.to(componentElement, 0.5, {
            'css': {'opacity': 0},
            'onComplete': self.hideComponent,
        });
    };
    this.displayComponent = function() {
        if (!displayed) {
            attach();

            displayed = true;

            componentElement.style.opacity = 0;
            componentElement.style.display = 'block';

            updatePosition();
            if (classNameReferralSelected) {
                domHelper.addClass(referralElement, classNameReferralSelected);
            }
            TweenLite.to(componentElement, 0.5, {'css': {'opacity': 1}});
        }
    };
    this.hideComponent = function(callBack) {
        displayed = false;
        // componentElement.style.display = 'none';
        if (classNameReferralSelected) {
            domHelper.removeClass(referralElement, classNameReferralSelected);
        }
        detach();
        if (callBack) {
            callBack();
        }
    };
    const updatePosition = function(e) {
        if (!displayed) {
            return;
        }
        let verticalMouseCoord = window.mouseTracker.mouseX;
        let verticalOffsetWidth = window.innerWidth;

        let referralStyle = getComputedStyle(referralElement);
        let referralPosition = referralElement.getBoundingClientRect();

        let popupStyle = getComputedStyle(componentElement);
        let popupHeight = parseFloat(popupStyle.height);
        let popupWidth = parseFloat(popupStyle.width);
        let maxWidth = parseFloat(window.innerWidth);
        if (hintPointer) {
            popupPointerStyle = getComputedStyle(componentElement, ':before');
        }
        let xPosition = 0;
        if (fixedX) {
            xPosition = fixedX;
        } else if (!fixedX && !hintPointer) {
            xPosition = window.mouseTracker.mouseX + popupOffset;
            if (verticalOffsetWidth - verticalMouseCoord < popupWidth) {
                xPosition = xPosition - popupWidth;
            }
        } else if (hintPointer) {
            let popupPointerWidth = parseFloat(popupPointerStyle.width);
            let popupPointerRight = parseFloat(popupPointerStyle.right);
            let referralWidth = parseFloat(referralStyle.width) - parseFloat(referralStyle.paddingLeft) - parseFloat(referralStyle.paddingRight);
            let referralRight = referralPosition.right;
            xPosition = referralRight - referralWidth / 2 - popupWidth + popupPointerWidth / 2 + popupPointerRight;
        }

        let yPosition = 0;
        if (fixedY) {
            yPosition = fixedY - popupHeight;
        } else if (!fixedY && !hintPointer) {
            yPosition = window.mouseTracker.mouseY - popupHeight - popupOffset;
        } else if (hintPointer) {
            // let popupPointerHeight = parseFloat(popupPointerStyle.height);
            let referralHeight = parseFloat(referralStyle.height);

            yPosition = window.mouseTracker.mouseY - referralHeight / 2 - popupHeight;
        }


        componentElement.style.left = xPosition + 'px';
        componentElement.style.top = yPosition + 'px';
    };
    const isAChildOf = function(_parent, _child) {
        if (_parent === _child) {
            return false;
        }
        while (_child && _child !== _parent) {
            _child = _child.parentNode;
        }

        return _child === _parent;
    };
    const addMouseHandlers = function() {
        if (behaviourType === 'mouseover') {
            window.eventsManager.addHandler(window, 'resize', resizeHandler);
            window.eventsManager.addHandler(referralElement, 'mousemove', moveHandler);
            window.eventsManager.addHandler(referralElement, 'mouseover', overHandler);
            window.eventsManager.addHandler(referralElement, 'mouseenter', enterHandler);
            if (hideOnLeave) {
                window.eventsManager.addHandler(referralElement, 'mouseleave', leaveHandler);
            }
            if (hideOnClick) {
                window.eventsManager.addHandler(referralElement, 'click', leaveHandler);
            }
        }
        if (behaviourType === 'click') {
            window.eventsManager.addHandler(window, 'resize', resizeHandler);
            window.eventsManager.addHandler(referralElement, 'click', clickHandler);
        }
    };
    this.setDisplayDelay = function(delay) {
        displayDelay = delay;
    };
    this.setText = function(text) {
        popupText = text;
        contentElement.innerHTML = popupText;
        updatePosition();
    };
    this.setFixedCoordinates = function(x, y) {
        fixedX = x;
        fixedY = y;
        updatePosition();
    };
    this.changeBehaviour = function(newType) {
        window.eventsManager.removeHandler(window, 'resize', resizeHandler);
        if (referralElement) {
            window.eventsManager.removeHandler(referralElement, 'mousemove', moveHandler);
            window.eventsManager.removeHandler(referralElement, 'mouseover', overHandler);
            window.eventsManager.removeHandler(referralElement, 'mouseenter', enterHandler);
            window.eventsManager.removeHandler(referralElement, 'mouseleave', leaveHandler);
            window.eventsManager.removeHandler(referralElement, 'click', leaveHandler);
        }
        behaviourType = newType;
        addMouseHandlers();
    };
    init();
};