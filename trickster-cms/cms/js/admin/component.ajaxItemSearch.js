window.AjaxItemSearchComponent = function(parentElement, parameters) {
    var resultIdElement;
    var resultElement;
    var resultContainerElement;
    var componentElement;
    var searchInput;

    var init = function() {
        createDomStructure();
        parameters.clickCallback = ajaxSearchCallback;
        parameters.types = parameters.types || parentElement.getAttribute('data-types');
        new AjaxSearchComponent(searchInput, parameters);
    };

    var createDomStructure = function() {
        // container
        componentElement = document.createElement('div');
        componentElement.className = 'ajaxitemsearch_container';
        parentElement.appendChild(componentElement);

        // searchbox
        searchInput = document.createElement('input');
        var resultId = document.createElement('input');
        searchInput.type = 'text';
        resultId.type = 'hidden';
        searchInput.className = 'input_component ajaxitemsearch_searchinput';
        resultId.className = 'ajaxitemsearch_resultid';
        searchInput.placeholder = window.translationsLogics.get('field.search');
        componentElement.appendChild(searchInput);
        componentElement.appendChild(resultId);

        // icon
        var searchIcon = document.createElement('span');
        searchIcon.className = 'icon icon_search';
        componentElement.appendChild(searchIcon);

        // result
        var resultContainer = document.createElement('div');
        var resultText = document.createElement('span');
        var resultIconRemove = document.createElement('span');

        resultContainer.className = 'ajaxitemsearch_result';
        resultText.className = 'ajaxitemsearch_result_text';
        resultIconRemove.className = 'ajaxitemsearch_result_remover';

        componentElement.appendChild(resultContainer);
        resultContainer.appendChild(resultText);
        resultContainer.appendChild(resultIconRemove);

        resultIdElement = _('.ajaxitemsearch_resultid', componentElement)[0];
        resultContainerElement = _('.ajaxitemsearch_result', componentElement)[0];
        resultElement = _('.ajaxitemsearch_result_text', componentElement)[0];

        eventsManager.addHandler(resultIconRemove, 'click', removeResult);

        if (resultElement.innerHTML != '') {
            resultContainerElement.style.display = 'block';
        }
    };

    var ajaxSearchCallback = function(data) {
        if (data) {
            resultContainerElement.style.display = 'block';
            resultIdElement.value = data['id'];
            resultElement.innerHTML = data['title'];
            searchInput.value = '';
            searchInput.blur();
        }
    };

    var removeResult = function() {
        resultContainerElement.style.display = 'none';
        resultIdElement.value = '';
        resultElement.innerHTML = '';
    };
    init();
};