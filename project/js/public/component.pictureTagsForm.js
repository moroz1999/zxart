window.PictureTagsFormComponent = function(componentElement) {
	var tagSearch;
	var self = this;
	var init = function() {
		var inputElement = _('.picturetags_input', componentElement)[0];
		if (inputElement) {
			tagSearch = new TagSearchInputComponent(inputElement);

			var suggestionButtonElements = _('.tag_suggestion_button', componentElement);
			if (suggestionButtonElements) {
				for (var i = 0; i < suggestionButtonElements.length; i++) {
					new TagSuggestionButtonComponent(suggestionButtonElements[i], self);
				}
			}
		}
	};

	this.addText = function(text) {
		tagSearch.addText(text);
	};
	init();
};
if (!String.prototype.trim) {
	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g, '');
	};
}
if (!String.prototype.splice) {
	String.prototype.splice = function(idx, rem, s) {
		return (this.slice(0, idx) + s + this.slice(idx + Math.abs(rem)));
	};
}

window.TagSearchInputComponent = function(componentElement) {
	var init = function() {
		var parameters = {
			'clickCallback': clickHandler,
			'minLength': 1,
			'apiMode': 'public',
			'types': 'tag',
			'getValueCallback': getValueCallback
		};

		new AjaxSearchComponent(componentElement, parameters);
	};
	var clickHandler = function(data) {
		if (data.value) {
			var boundaries = getCurrentTagBoundaries(componentElement.value);
			var start = boundaries.start;
			var end = boundaries.end;

			componentElement.value = componentElement.value.splice(start, end - start, data.value) + ', ';
		}
	};
	var getValueCallback = function() {
		var boundaries = getCurrentTagBoundaries(componentElement.value);
		var start = boundaries.start;
		var end = boundaries.end;

		return componentElement.value.slice(start, end);
	};
	var getCurrentTagBoundaries = function(text) {
		var position = getCursorPosition(componentElement);

		var start = position;
		var end = position;
		for (var i = position; i >= 0; i--) {
			if (text[i] != ',' && text[i] != ' ') {
				start = i;
			}
			if (text[i] == ',' && i != position) {
				break;
			}
		}

		for (i = position; i <= text.length; i++) {
			if (text[i] != ',') {
				end = i;
			}
			else {
				break;
			}
		}
		return {'start': start, 'end': end};
	};
	var getCursorPosition = function(input) {
		if ('selectionStart' in input) {
			return input.selectionStart;
		}
		else if (document.selection) {
			var sel = document.selection.createRange();
			var selLen = document.selection.createRange().text.length;
			sel.moveStart('character', -input.value.length);
			return sel.text.length - selLen;
		}
		return false;
	};
	this.addText = function(text) {
		if ((componentElement.value != '') && (componentElement.value.substr(-2, 2) != ", ") && (componentElement.value.substr(-1, 1) != ",")) {
			componentElement.value += ', ';
		}
		componentElement.value += text + ', ';
	};
	init();
};
window.TagSuggestionButtonComponent = function(componentElement, formComponent) {
	var textElement;
	var init = function() {
		if (textElement = _('.tag_suggestion_text', componentElement)[0]) {
			eventsManager.addHandler(componentElement, 'click', clickHandler);
		}
	};
	var clickHandler = function() {
		var text = textElement.textContent;
		formComponent.addText(text);

		componentElement.parentNode.removeChild(componentElement);
	};
	init();
};