window.ModalComponent = function (options) {
    DomElementMakerMixin.call(this);

    let self = this;
    let componentElement;
    let contentElement;
    let footerElement;
    let titleElement;
    let CLASS_OPEN = 'is_open';
    let displayed = false;
    let headerEnabled = true;
    let footerEnabled = true;
    let componentClassName;
    let onCloseCallback;
    const init = function () {
        if (options) {
            parseOptions(options);
        }
        createDom();
        controller.addListener('modalsClose', globalCloseHandler);
    };
    const parseOptions = function (options) {
        if (typeof options.headerEnabled !== 'undefined') {
            headerEnabled = options.headerEnabled;
        }
        if (typeof options.footerEnabled !== 'undefined') {
            footerEnabled = options.footerEnabled;
        }
        if (typeof options.componentClassName !== 'undefined') {
            componentClassName = options.componentClassName;
        }
        if (typeof options.onCloseCallback === 'function') {
            onCloseCallback = options.onCloseCallback;
        }
    };
    const createDom = function () {
        let makeElement = self.makeElement;
        componentElement = makeElement('div', 'modal');
        let closeButton;
        if (headerEnabled) {
            let headerElement;
            headerElement = makeElement('div', 'modal_header', componentElement);
            titleElement = makeElement('div', 'modal_title', headerElement);

            closeButton = makeElement('div', 'modal_closebutton', headerElement);
        } else {
            titleElement = makeElement('div', 'modal_title', componentElement);
            closeButton = makeElement('div', 'modal_closebutton', componentElement);
        }

        closeButton.addEventListener('click', self.modalCloseClick);
        contentElement = makeElement('div', 'modal_content', componentElement);

        if (footerEnabled) {
            footerElement = makeElement('div', 'modal_footer', componentElement);
        }

        if (componentClassName) {
            componentElement.classList.add(componentClassName);
        }
    };
    const globalCloseHandler = function () {
        self.setDisplayed(false);
    };
    this.modalCloseClick = function (event) {
        event.preventDefault();
        self.setDisplayed(false);
    };
    this.addClass = function (newClass) {
        domHelper.addClass(componentElement, newClass);
    };
    this.removeClass = function (newClass) {
        domHelper.removeClass(componentElement, newClass);
    };
    this.setTitle = function (title) {
        if (titleElement) {
            titleElement.innerHTML = title;
        }
    };
    this.setContentElement = function (element) {
        while (contentElement.firstChild) {
            contentElement.removeChild(contentElement.firstChild);
        }
        contentElement.appendChild(element);
        contentElement.scrollTop = 0;
    };
    this.setContentHtml = function (html) {
        contentElement.innerHTML = html;
        contentElement.scrollTop = 0;
    };
    this.setControls = function (element) {
        if (footerElement) {
            while (footerElement.firstChild) {
                footerElement.removeChild(footerElement.firstChild);
            }
            footerElement.appendChild(element);
        }
    };
    this.setDisplayed = function (newDisplayed) {
        if (newDisplayed) {
            controller.fireEvent('modalsClose');
        }
        if (displayed === newDisplayed) {
            return;
        }
        displayed = newDisplayed;
        if (displayed) {
            document.documentElement.classList.add('has_modal');
            document.body.appendChild(componentElement);
            self.addClass(CLASS_OPEN);
        } else {
            document.documentElement.classList.remove('has_modal');
            document.body.removeChild(componentElement);
            self.removeClass(CLASS_OPEN);
            if (onCloseCallback) {
                onCloseCallback();
            }
        }
        contentElement.scrollTop = 0;
    };
    this.toggleDisplay = function () {
        self.setDisplayed(!displayed);
    };
    this.getComponentElement = function () {
        return componentElement;
    };
    init();
};
