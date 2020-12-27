window.JoinTagFormComponent = function(componentElement) {
	var inputElement;
	var valueElement;
	var init = function() {
		if (valueElement = _('.jointag_value', componentElement)[0]) {
			if (inputElement = _('.jointag_input', componentElement)[0]) {
				var parameters = {
					'clickCallback': clickHandler,
					'minLength': 1,
					'apiMode': 'admin',
					'types': 'tag',
					'getValueCallback': getValueCallback
				};

				var search = new AjaxSearchComponent(inputElement, parameters);
				search.setFilters('structureSkipId=' + inputElement.dataset.id);
			}
		}
	};
	var getValueCallback = function() {
		return inputElement.value;
	};
	var clickHandler = function(data) {
		if (data.id) {
			inputElement.value = data.value;
			valueElement.value = data.id;
		}
	};

	init();
};