window.SpoilerComponent = function(componentElement) {
	var titleElement;
	var contentElement;
	var contentWrapperElement;
	var buttonElement;
	var gradientComponent;

	var maxHeight;
	var minHeight;

	var showMoreText;
	var showLessText;

	var init = function() {
		titleElement = componentElement.querySelector('.spoiler_component_title');
		contentElement = componentElement.querySelector('.spoiler_component_content');
		contentWrapperElement = componentElement.querySelector('.spoiler_component_content_wrapper');
		gradientComponent = componentElement.querySelector('.partly_hidden_gradient');

		if (titleElement && contentElement) {
			if (componentElement.classList.contains('spoiler_partly_hidden')) {
				initGradientElement();
			}
			if (componentElement.classList.contains('spoiler_button_element')) {
				initButtonElement('spoiler_button_element');
			}
			var computedStyles = getComputedStyle(contentElement);
			showLessText = window.translationsLogics.get('spoiler.view_less_info');
			maxHeight = computedStyles.height;
			contentElement.style.height = maxHeight;
			showMoreText = buttonElement.innerHTML;
			addHandlers();
		}
	};

	var addHandlers = function() {
		if (buttonElement) {
			buttonElement.addEventListener('click', onClick);
		} else {
			titleElement.addEventListener('click', onClick);
		}

	};

	var onClick = function() {
		if (isShow()) {
			hideElement();
		} else {
			showElement();
		}
	};

	var hideElement = function() {
		var height = contentElement.scrollHeight;
		TweenLite.to(contentElement, 0.5,
			{
				'css': {
					'height': height
				},
				onStart: function() {
					TweenLite.to(gradientComponent, 0.5, {
						'css': {
							'background': 'transparent'
						}
					});
					buttonElement.innerHTML = showLessText;
				}
			}
		);
	};

	var showElement = function() {
		TweenLite.to(contentElement, 0.5, {
			'css': {
				'height': maxHeight
			},
			onStart: function() {
				TweenLite.to(gradientComponent, 0.5, {
					'css': {
						'background': 'linear-gradient(transparent, #fff)'
					}
				});
				buttonElement.innerHTML = showMoreText;
			}
		});
	};

	var isShow = function() {
		if (contentElement.style.height == maxHeight) {
			return true;
		} else {
			return false;
		}
	};

	var initGradientElement = function() {
		gradientComponent = componentElement.querySelector('.spoiler_partly_hidden_gradient');
		if (!gradientComponent) {
			gradientComponent = document.createElement('div');
			gradientComponent.classList.add('spoiler_partly_hidden_gradient');

			if (contentWrapperElement) {
				contentWrapperElement.appendChild(gradientComponent);
			}
		}
	};

	var initButtonElement = function(className) {
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