window.RadioTabsComponent = function(componentElement) {
    var buttons;
    var init = function() {
        buttons = componentElement.querySelectorAll('.button');

        reCheck();
        for (var i = 0; i < buttons.length; i++) {
            eventsManager.addHandler(buttons[i], 'click', reCheck);
        }

    };

    var reCheck = function() {
        for (var i = 0; i < buttons.length; i++) {
            var input = buttons[i].querySelector('input');
            if (input.checked) {
                domHelper.addClass(buttons[i], 'success_button');
            } else {
                domHelper.removeClass(buttons[i], 'success_button');
            }
        }
    };

    init();
};
DomHelperMixin.call(FormHelperComponent.prototype);