window.showFiltersComponent = function(componentElement) {
    var showFiltersElement;
    var formFiltersShowedElements;

    var init = function() {
        if (showFiltersElement = componentElement.querySelector('input.show_filters')) {
            formFiltersShowedElements = componentElement.querySelectorAll('.form_filters_showed');
            eventsManager.addHandler(showFiltersElement, 'change', checkDisplay);
            checkDisplay();
        }

    };
    var checkDisplay = function() {
        var i;
        if (showFiltersElement.checked) {
            for (i = formFiltersShowedElements.length; i--;) {
                formFiltersShowedElements[i].style.display = 'none';
            }
        } else {
            for (i = formFiltersShowedElements.length; i--;) {
                formFiltersShowedElements[i].style.display = '';
            }
        }
    };
    init();
};