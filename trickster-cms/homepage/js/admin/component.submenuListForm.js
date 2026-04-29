window.SubmenuListFormComponent = function(componentElement) {
    var init = function() {
        if (typeSelector = _('select.submenulist_form_type', componentElement)[0]) {
            eventsManager.addHandler(typeSelector, 'change', changeHandler);
            menusRowElement = _('.submenulist_form_menus', componentElement)[0].parentElement.parentElement;
        }
        refreshState();
    };
    var changeHandler = function() {
        refreshState();
    };
    var refreshState = function() {
        if (typeSelector.value === 'auto') {
            menusRowElement.style.display = 'none';
        } else {
            menusRowElement.style.display = 'table-row';
        }
    };
    var self = this;
    var typeSelector = false;
    var menusRowElement = false;

    init();
};