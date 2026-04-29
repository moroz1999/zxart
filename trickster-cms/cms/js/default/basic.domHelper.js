window.domHelper = new function() {
    this.setTextContent = function(element, text) {
        if (typeof element == 'object' && typeof text !== 'undefined') {
            while (element.firstChild) {
                element.removeChild(element.firstChild);
            }
            var textNode = document.createTextNode(text);
            element.appendChild(textNode);
        }
    };
    this.formatNumber = function(number, decimals) {
        number = number.toString();
        if (number.length < decimals) {
            for (var a = decimals - number.length; a > 0; a--) {
                number = '0' + number;
            }
        }
        return number;
    };
    this.roundNumber = function(number, precision) {
        //workaround for IEEE-754 rounding problem. try alert((1.325).toFixed(2)) to illustrate the problem
        return (Math.round(number * 100) / 100).toFixed(precision);
    };
    this.addClass = function(element, className) {
        if (element) {
            var elementClassName = element.className + '';
            if (-1 == elementClassName.indexOf(className)) {
                if (elementClassName == '') {
                    element.className = className;
                } else {
                    element.className += ' ' + className;
                }
            }
        }
    };
    this.removeClass = function(element, className) {
        if (element) {
            var elementClassName = element.className + '';
            if (-1 != elementClassName.indexOf(className)) {
                if (-1 != elementClassName.indexOf(className + ' ')) {
                    className += ' ';
                } else if (-1 != elementClassName.indexOf(' ' + className)) {
                    className = ' ' + className;
                }
                elementClassName = elementClassName.replace(className, '');
                element.className = elementClassName;
            }
        }
    };
    this.getElementPositions = function(domElement) {
        var elementLeft = 0;
        var elementTop = 0;

        if (domElement.offsetParent) {
            elementLeft = domElement.offsetLeft;
            elementTop = domElement.offsetTop;
            while (domElement = domElement.offsetParent) {
                if (domElement.tagName != 'body' && domElement.tagName != 'BODY') {
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
    this.isAChildOf = function(_parent, _child) {
        if (_parent === _child) {
            return false;
        }
        while (_child && _child !== _parent) {
            _child = _child.parentNode;
        }

        return _child === _parent;
    };

    this.hasClass = function(element, cls) {
        return (' ' + element.className + ' ').indexOf(' ' + cls + ' ') > -1;
    };
};