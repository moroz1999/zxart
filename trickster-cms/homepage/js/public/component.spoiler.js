window.SpoilerComponent = function(componentElement) {
    let titleElement;
    let contentElement;
    let contentWrapperElement;
    let buttonElement;
    let gradientComponent;
    let plusComponent;

    let maxHeight;
    let minHeight = 0;

    let showMoreText;
    let showLessText;

    const hideContentClass = 'spoiler_hidden';

    let visible;

    let self = this;

    let init = function() {
        contentWrapperElement = componentElement.querySelector('.spoiler_component_content_wrapper');
        contentElement = contentWrapperElement.querySelector('.spoiler_component_content');
        gradientComponent = componentElement.querySelector('.partly_hidden_gradient');
        if (titleElement = componentElement.querySelector('.spoiler_component_title')) {
            plusComponent = document.createElement('span');
            plusComponent.className = 'spoiler_component_plus';
            titleElement.appendChild(plusComponent);
        }

        if (titleElement && contentElement) {
            maxHeight = contentElement.scrollHeight + 'px';
            let computedStyles = getComputedStyle(contentWrapperElement);
            if (componentElement.classList.contains('spoiler_partly_hidden')) {
                initGradientElement();
                minHeight = computedStyles.minHeight;
            }
            if (componentElement.classList.contains('spoiler_button_element')) {
                initButtonElement('spoiler_button_element');
                showLessText = window.translationsLogics.get('spoiler.view_less_info');
                showMoreText = buttonElement.innerHTML;
            }
            visible = !(componentElement.classList.contains(hideContentClass));
            if (visible) {
                if (maxHeight === '0px') {
                    maxHeight = 'auto';
                }
                contentWrapperElement.style.height = maxHeight;
            } else {
                contentWrapperElement.style.height = '0px';
            }
            maxHeight = contentElement.scrollHeight + 'px';
            addHandlers();
        }
    };

    let resize = function() {
        maxHeight = contentElement.scrollHeight + 'px';
        if (visible) {
            self.showElement();
        }
    };

    let addHandlers = function() {
        if (buttonElement) {
            buttonElement.addEventListener('click', toggleVisibility);
        } else {
            titleElement.addEventListener('click', toggleVisibility);
        }
        eventsManager.addHandler(window, 'resize', resize);

    };

    let toggleVisibility = function() {
        if (visible) {
            self.hideElement();
        } else {
            self.showElement();
        }
    };

    this.hideElement = function() {
        visible = false;
        componentElement.classList.add(hideContentClass);

        TweenLite.to(contentWrapperElement, 0.5,
            {
                'css': {
                    'height': minHeight
                },
                onStart: function() {
                    if (gradientComponent) {
                        TweenLite.to(gradientComponent, 0.5, {
                            'css': {
                                'background': 'linear-gradient(transparent, #fff)'
                            }
                        });
                    }
                },
                onComplete: function() {
                    if (buttonElement) {
                        buttonElement.innerHTML = showLessText;
                    }
                }
            }
        );
    };

    this.showElement = function() {
        visible = true;
        componentElement.classList.remove(hideContentClass);

        maxHeight = contentElement.clientHeight + 'px';
        TweenLite.to(contentWrapperElement, 0.5, {
            'css': {
                'height': maxHeight
            },
            onStart: function() {
                if (gradientComponent) {
                    TweenLite.to(gradientComponent, 0.5, {
                        'css': {
                            'background': 'transparent'
                        }
                    });
                }
            },
            onComplete: function() {
                if (buttonElement) {
                    buttonElement.innerHTML = showMoreText;
                }
            }
        });
    };

    let initGradientElement = function() {
        gradientComponent = componentElement.querySelector('.spoiler_partly_hidden_gradient');
        if (!gradientComponent) {
            gradientComponent = document.createElement('div');
            gradientComponent.classList.add('spoiler_partly_hidden_gradient');

            if (contentWrapperElement) {
                contentWrapperElement.appendChild(gradientComponent);
            }
        }
    };

    let initButtonElement = function(className) {
        buttonElement = componentElement.querySelector('.' + className);
        if (!buttonElement) {
            buttonElement = document.createElement('button');
            buttonElement.classList.add(className);
            buttonElement.innerHTML = showMoreText;
            componentElement.appendChild(buttonElement);
        }
        componentElement.classList.remove(className);
    };

    init();
};