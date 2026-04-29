window.BubbleComponent = function(referralElement, message, additionalContainerClassName, additionalClassName, bubbleCloseTag, waitDelay) {
    waitDelay = waitDelay ? waitDelay : 1000;

    var componentElement = false;
    var contentElement = false;
    var contentElementClassName;
    var headerElement = false;
    var middleElement = false;
    var footerElement = false;

    var positionedX = false;
    var positionedY = false;
    var bubbleWidth = false;
    var bubbleLeft;
    var bubbleTopStart;
    var bubbleTopStop;
    var positionedW;
    var positionedH;
    var bubbleTopFinish;

    var bubbleHeight;
    var bubbleHeightDigit;

    var init = function() {
        componentElement = document.createElement('div');
        componentElement.className = 'tip_popup ' + additionalContainerClassName;
        componentElement.style.display = 'none';

        contentElement = document.createElement('div');
        contentElementClassName = 'tip_popup_content ' + additionalClassName;
        contentElement.className = contentElementClassName;

        headerElement = document.createElement('div');
        headerElement.className = 'notice_header';
        middleElement = document.createElement('div');
        middleElement.className = 'notice_middle';
        footerElement = document.createElement('div');
        footerElement.className = 'notice_footer';

        if (message && message['title']) {
            headerElement.innerHTML = '<span class="notice_title">' + message['title'] + '</span>';
            contentElement.appendChild(headerElement);
        }
        if (message && message['content']) {
            middleElement.innerHTML = message['content'];
            contentElement.appendChild(middleElement);
        }
        if (message && message['footer']) {
            footerElement.innerHTML = message['footer'];
            contentElement.appendChild(footerElement);
        }

        componentElement.appendChild(contentElement);

        document.body.appendChild(componentElement);

    };
    this.start = function() {
        componentElement.style.display = 'block';
        //document.scrollingElement is required for Safari.
        const html = document.scrollingElement || document.documentElement;
        var htmlScroll = html.scrollTop;

        var parentPositionsX;
        var parentPositionsY;
        if (referralElement.dataset.notice) {
            var noticeParentSelector = referralElement.dataset.notice;
            parentPositionsX = document.querySelector(noticeParentSelector).getBoundingClientRect();
        } else {
            parentPositionsX = referralElement.getBoundingClientRect(); // span-checkbox
        }
        parentPositionsY = referralElement.getBoundingClientRect(); // span-checkbox

        positionedX = parentPositionsX.left;
        // positionedX = parentPositions.left;
        positionedW = parentPositionsX.width;
        positionedY = parentPositionsY.top;
        positionedH = parentPositionsY.height;

        var bubbleGetCompStyle = getComputedStyle(componentElement);
        bubbleHeight = bubbleGetCompStyle.height;
        bubbleHeightDigit = parseFloat(bubbleHeight);

        var startY = positionedY + htmlScroll;
        var marginBottom = 20;
        bubbleLeft = positionedX + 'px';
        bubbleTopStart = startY + 'px';
        bubbleTopStop = Math.ceil(startY - bubbleHeightDigit - marginBottom) + 'px';
        bubbleTopFinish = Math.ceil(startY - positionedH - (bubbleHeightDigit * 2) - positionedH) + 'px';
        componentElement.style.top = bubbleTopStart;
        componentElement.style.left = bubbleLeft;
        componentElement.style.width = bubbleWidth;
        componentElement.style.fontSize = 0;
        componentElement.style.lineHeight = 0;
        componentElement.style.height = 0;
        componentElement.style.overflow = 'hidden';
        componentElement.style.opacity = 0;
        TweenLite.to(componentElement,
            0.5,
            {
                'css': {
                    'opacity': 1,
                    'fontSize': 'inherit',
                    'lineHeight': '1',
                    'height': bubbleHeight,
                    'minHeight': bubbleHeight,
                    'overflow': 'visible',
                    'left': bubbleLeft,
                    'top': bubbleTopStop
                },
                'onComplete': wait
            });

        if (bubbleCloseTag) {
            document.querySelector('.' + additionalClassName + ' .' + bubbleCloseTag).addEventListener('click', function(ev) {
                ev.preventDefault();
                componentElement.parentNode.removeChild(componentElement);
            });
        }
    };
    var wait = function() {
        window.setTimeout(completeAnimation, waitDelay);
    };
    var completeAnimation = function() {
        TweenLite.to(componentElement, 0.5, {
            'css': {'opacity': 0, 'left': bubbleLeft, 'top': bubbleTopFinish},
            'onComplete': destroyComponent
        });
    };
    var destroyComponent = function() {
        if (componentElement) {
            componentElement.parentNode.removeChild(componentElement);
        }
    };

    init();
};