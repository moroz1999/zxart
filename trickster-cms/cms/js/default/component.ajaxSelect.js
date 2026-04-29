window.AjaxSelectComponent = function(selectElement, types, apiMode, changeCallback, filters) {
    var self = this;

    this.componentElement = null;
    this.selectElement = selectElement;

    var inputElement;
    var ajaxSelectItems = {};
    var searchComponent;
    var itemList;

    var resultIdElement;
    var resultElement;
    var resultContainerElement;
    var componentElement;

    var init = function() {
        createDom();

        var parameters = {
            'types': types,
            'apiMode': apiMode,
            'clickCallback': clickCallback,
            'filters': filters,
        };
        searchComponent = new AjaxSearchComponent(inputElement, parameters);

        eventsManager.addHandler(window, 'resize', refreshStatus);
    };

    var createDom = function() {
        if (!selectElement) {
            selectElement = document.createElement('select');
        }
        selectElement.style.display = 'none';

        // container
        self.componentElement = document.createElement('div');
        self.componentElement.className = 'ajaxselect_container';

        // searchbox
        inputElement = document.createElement('input');
        inputElement.type = 'text';
        inputElement.className = 'ajaxselect_searchinput';
        inputElement.placeholder = window.translationsLogics.get('field.search');
        self.componentElement.appendChild(inputElement);
        // icon
        var searchIcon = document.createElement('span');
        searchIcon.className = 'icon icon_search';
        self.componentElement.appendChild(searchIcon);

        // selected items
        itemList = document.createElement('div');
        itemList.className = 'ajaxselect_item_list';
        self.componentElement.appendChild(itemList);

        if (selectElement.parentNode) {
            selectElement.parentNode.insertBefore(self.componentElement, selectElement);
        }
        for (var i = 0; i < selectElement.options.length; ++i) {
            if (selectElement.options[i].innerHTML && selectElement.options[i].value != '' && selectElement.options[i].selected) {
                self.importOptionElement(selectElement.options[i]);
            }
        }
    };

    var refreshStatus = function() {
        var positions = self.componentElement.getBoundingClientRect();

        itemList.style.width = self.componentElement.offsetWidth;
        itemList.top = positions.top;
        itemList.left = positions.left;
    };

    var clickCallback = function(data) {
        inputElement.blur();
        inputElement.value = '';
        self.addOption(data);
    };

    this.setFilters = function(filterString) {
        searchComponent.setFilters(filterString);
    };

    this.importOptionElement = function(optionElement) {
        var selectItem = new AjaxSelectItemComponent(self, optionElement, selectElement.multiple);
        ajaxSelectItems[optionElement.value] = selectItem;
        itemList.appendChild(selectItem.getComponentElement());
    };

    this.addOption = function(data) {
        if (!selectElement.multiple && selectElement.length > 0) {
            self.removeAllOptions();
        }

        var optionElement = document.createElement('option');
        optionElement.selected = true;
        optionElement.text = data.title;
        optionElement.value = data.id;
        optionElement.setAttribute('selected', 'selected');
        selectElement.appendChild(optionElement);
        self.importOptionElement(optionElement);
        if (typeof changeCallback == 'function') {
            changeCallback(data);
        }
    };

    this.removeOption = function(optionElement) {
        selectElement.removeChild(optionElement);
        var itemElement = ajaxSelectItems[optionElement.value].getComponentElement();
        itemList.removeChild(itemElement);
        delete ajaxSelectItems[optionElement.value];
        if (typeof changeCallback == 'function') {
            changeCallback();
        }
    };

    this.removeAllOptions = function() {
        var resultContainer = _('.ajaxselect_item_list', self.componentElement)[0];
        while (selectElement.firstChild) {
            selectElement.removeChild(selectElement.firstChild);
        }
        for (var key in ajaxSelectItems) {
            resultContainer.removeChild(ajaxSelectItems[key].getComponentElement());
        }
        ajaxSelectItems = {};
    };

    this.getValues = function() {
        var values = [];
        for (var value in ajaxSelectItems) {
            values[values.length] = value;
        }
        return values;
    };

    this.getTypes = function() {
        return types;
    };

    this.getComponentElement = function() {
        return self.componentElement;
    };

    init();
};

window.AjaxSelectItemComponent = function(ajaxSelectComponent, optionElement, multiple) {
    var componentElement;
    var descriptionElement;
    var removalElement;

    var init = function() {
        if (multiple === undefined) {
            multiple = true;
        }
        createDom();
        eventsManager.addHandler(removalElement, 'click', removeClick);
    };
    var createDom = function() {
        componentElement = document.createElement('div');
        descriptionElement = document.createElement('span');
        descriptionElement.innerHTML = optionElement.text;
        removalElement = document.createElement('div');
        if (!multiple) {
            componentElement.className = 'ajaxitemsearch_result ajaxitemsearch_result_single';
            descriptionElement.className = 'ajaxitemsearch_result_text';
            removalElement.className = 'ajaxitemsearch_result_remover';
        } else {
            componentElement.className = 'ajaxselect_item';
            descriptionElement.className = 'ajaxselect_item_value';
            removalElement.className = 'ajaxselect_item_remover';
        }

        componentElement.appendChild(removalElement);
        componentElement.appendChild(descriptionElement);
    };

    var removeClick = function(event) {
        ajaxSelectComponent.removeOption(optionElement);
    };

    this.getComponentElement = function() {
        return componentElement;
    };

    init();
};