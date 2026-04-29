window.TranslationFormComponent = function(componentElement) {
    var typeSelectElement;
    var dynamicElements = {};

    var init = function() {
        typeSelectElement = _('select.translation_form_type', componentElement)[0];
        dynamicElements['text'] = _('.translation_form_text_related', componentElement);
        dynamicElements['textarea'] = _('.translation_form_textarea_related', componentElement);
        dynamicElements['html'] = _('.translation_form_html_related', componentElement);
        refreshForm();
        eventsManager.addHandler(typeSelectElement, 'change', refreshForm);
    };
    var refreshForm = function() {
        var selection = typeSelectElement.options[typeSelectElement.selectedIndex].value;
        for (var type in dynamicElements) {
            if (type == selection) {
                for (var i = dynamicElements[type].length; i--;) {
                    dynamicElements[type][i].style.display = '';
                }
            } else {
                for (var i = dynamicElements[type].length; i--;) {
                    dynamicElements[type][i].style.display = 'none';
                }
            }
        }
    };
    init();
};