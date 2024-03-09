function DetailedSearchFormComponent(componentElement) {
	var formElement;

	var titleInput;
	var startYearInput;
	var endYearInput;
	var ratingInput;
	var partyPlaceInput;
	var musicFormatGroupSelect;
	var musicFormatSelect;
	var pictureTypeSelect;
	var sortParameterSelect;
	var sortOrderSelect;
	var realtimeCheckbox;
	var inspirationCheckbox;
	var stagesCheckbox;
	var tagsIncludeInput;
	var tagsExcludeInput;
	var authorCountrySelect;
	var authorCitySelect;
	var resultsTypeSelect;

	var submitButton;

	var init = function() {
		if (formElement = _('.detailedsearch_form', componentElement)[0]) {
			titleInput = _('.detailedsearch_title', formElement)[0];
			startYearInput = _('.detailedsearch_startyear', formElement)[0];
			endYearInput = _('.detailedsearch_endyear', formElement)[0];
			ratingInput = _('.detailedsearch_rating', formElement)[0];
			partyPlaceInput = _('.detailedsearch_partyplace', formElement)[0];
			pictureTypeSelect = _('select.detailedsearch_picturetype', formElement)[0];
			musicFormatGroupSelect = _('select.detailedsearch_formatgroup', formElement)[0];
			musicFormatSelect = _('select.detailedsearch_format', formElement)[0];
			sortParameterSelect = _('select.detailedsearch_sortparameter', formElement)[0];
			sortOrderSelect = _('select.detailedsearch_sortorder', formElement)[0];
			realtimeCheckbox = _('input.detailedsearch_realtime', formElement)[0];
			inspirationCheckbox = _('input.detailedsearch_inspiration', formElement)[0];
			stagesCheckbox = _('input.detailedsearch_stages', formElement)[0];
			if (tagsIncludeInput = _('.detailedsearch_tagsinclude', formElement)[0]) {
				new TagSearchInputComponent(tagsIncludeInput);
			}
			if (tagsExcludeInput = _('.detailedsearch_tagsexclude', formElement)[0]) {
				new TagSearchInputComponent(tagsExcludeInput);
			}
			if (authorCountrySelect = _('.detailedsearch_author_country', formElement)[0]) {
				new AjaxSelectComponent(authorCountrySelect, 'country', 'public');
			}
			if (authorCitySelect = _('.detailedsearch_author_city', formElement)[0]) {
				new AjaxSelectComponent(authorCitySelect, 'city', 'public');
			}
			if (resultsTypeSelect = _('select.detailedsearch_resultstype', formElement)[0]) {
			}
			if (submitButton = _('.detailedsearch_button', formElement)[0]) {
				eventsManager.addHandler(submitButton, 'click', submitForm);
			}
			eventsManager.addHandler(formElement, 'keydown', checkKey);
			eventsManager.addHandler(formElement, 'reset', function() {
				window.setTimeout(resetHandler, 0)
			});
		}
	};
	var resetHandler = function() {
		if (pictureTypeSelect) {
			eventsManager.fireEvent(pictureTypeSelect, 'change');
		}
		if (musicFormatGroupSelect) {
			eventsManager.fireEvent(musicFormatGroupSelect, 'change');
		}
		if (musicFormatSelect) {
			eventsManager.fireEvent(musicFormatSelect, 'change');
		}
		if (sortParameterSelect) {
			eventsManager.fireEvent(sortParameterSelect, 'change');
		}
		if (sortOrderSelect) {
			eventsManager.fireEvent(sortOrderSelect, 'change');
		}

	};
	var checkKey = function(event) {
		if (event.keyCode == 13) {
			submitForm();
		}
	};
	var submitForm = function(event) {
		if (event) {
			eventsManager.preventDefaultAction(event);
		}
		var url = formElement.getAttribute('action');

		if (titleInput && titleInput.value != '') {
			url += 'titleWord:' + titleInput.value + '/';
		}
		if (startYearInput && startYearInput.value != '') {
			url += 'startYear:' + startYearInput.value + '/';
		}
		if (endYearInput && endYearInput.value != '') {
			url += 'endYear:' + endYearInput.value + '/';
		}
		if (ratingInput && ratingInput.value != '') {
			url += 'rating:' + ratingInput.value + '/';
		}
		if (partyPlaceInput && partyPlaceInput.value != '') {
			url += 'partyPlace:' + partyPlaceInput.value + '/';
		}
		if (pictureTypeSelect && pictureTypeSelect.value != '') {
			url += 'pictureType:' + pictureTypeSelect.value + '/';
		}
		if (musicFormatGroupSelect && musicFormatGroupSelect.value != '') {
			url += 'formatGroup:' + musicFormatGroupSelect.value + '/';
		}
		if (musicFormatSelect && musicFormatSelect.value != '') {
			url += 'format:' + musicFormatSelect.value + '/';
		}
		if (sortParameterSelect && sortParameterSelect.value != '') {
			url += 'sortParameter:' + sortParameterSelect.value + '/';
		}
		if (sortOrderSelect && sortOrderSelect.value != '') {
			url += 'sortOrder:' + sortOrderSelect.value + '/';
		}
		if (realtimeCheckbox && realtimeCheckbox.checked != '') {
			url += 'realtime:' + 1 + '/';
		}
		if (stagesCheckbox && stagesCheckbox.checked != '') {
			url += 'stages:' + 1 + '/';
		}
		if (inspirationCheckbox && inspirationCheckbox.checked != '') {
			url += 'inspiration:' + 1 + '/';
		}
		if (tagsIncludeInput && tagsIncludeInput.value != '') {
			url += 'tagsInclude:' + encodeURIComponent(tagsIncludeInput.value) + '/';
		}
		if (tagsExcludeInput && tagsExcludeInput.value != '') {
			url += 'tagsExclude:' + encodeURIComponent(tagsExcludeInput.value) + '/';
		}
		if (authorCountrySelect && authorCountrySelect.value != '') {
			url += 'authorCountry:' + getSelectValuesString(authorCountrySelect) + '/';
		}
		if (authorCitySelect && authorCitySelect.value != '') {
			url += 'authorCity:' + getSelectValuesString(authorCitySelect) + '/';
		}
		if (resultsTypeSelect && resultsTypeSelect.value != '') {
			url += 'resultsType:' + resultsTypeSelect.value + '/';
		}
		document.location.href = url;
	};
	var getSelectValuesString = function(select) {
		var values = [];
		for (var i = 0, iLen = select.options.length; i < iLen; i++) {
			if (select.options[i].selected) {
				values.push(select.options[i].value);
			}
		}
		return values.join(',');
	};
	init();
}