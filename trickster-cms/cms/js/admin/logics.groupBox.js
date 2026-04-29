window.groupBoxLogics = new function() {
    var containerElements;
    var initComponents = function() {
        containerElements = document.querySelectorAll('.content_list');
        hangEvent();
        containerElements = document.querySelectorAll('.translations_export_table');
        hangEvent();
    };

    var hangEvent = function() {
        for (var i = 0; i < containerElements.length; i++) {
            var groupElement = containerElements[i].querySelector('input.groupbox');
            if (groupElement) {
                new GroupBoxComponent(groupElement, containerElements[i]);
            }
        }
    };
    controller.addListener('initDom', initComponents);
};