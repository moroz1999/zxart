window.animationLogics = new function() {
    var init = function() {
        addNewElement();
    };

    var addNewElement = function() {
        var button = document.querySelector('.addnewelement_button');
        var element = document.querySelector('.addnewelement_popup_items');
        var animation = 'sign';

        new animationComponent(button, element, animation);
    };

    controller.addListener('initDom', init);
};