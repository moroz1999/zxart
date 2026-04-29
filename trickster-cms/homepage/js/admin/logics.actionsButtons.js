window.actionsButtonsLogics = new function() {
    var controlsElements;
    var containerElements;
    var initComponents = function() {
        controlsElements = document.querySelector('.content_list_controls');
        containerElements = document.querySelector('.content_list');
        if (controlsElements && containerElements) {
            new ActionsButtonsComponent(controlsElements, containerElements);
        }
        if (controlsElements && !containerElements) {
            new ActionsButtonsComponent(controlsElements, false);
        }
    };
    controller.addListener('initDom', initComponents);
};