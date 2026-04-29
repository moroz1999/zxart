window.FeedbackFormComponent = function(componentElement) {
    var fileInputElement;
    var dropAreaElement;
    var dropHideTimeout;

    var init = function() {
        new AjaxFormComponent(componentElement, tracking.feedbackTracking);

        if (fileInputElement = componentElement.querySelector('.fileinput_placeholder')) {
            dropAreaElement = componentElement;
            dropAreaElement.addEventListener('dragover', dropAreaDragEnterHandler, true);
            dropAreaElement.addEventListener('dragenter', dropAreaDragEnterHandler, true);
            dropAreaElement.addEventListener('dragleave', dropAreaDragLeaveHandler, true);
            dropAreaElement.addEventListener('drop', dropAreaDropHandler, true);
        }
    };
    var dropAreaDropHandler = function(event) {
        eventsManager.cancelBubbling(event);
        eventsManager.preventDefaultAction(event);
        hideDropArea();
        importFilesInfo(event.dataTransfer.files);
    };
    var dropAreaDragLeaveHandler = function(event) {
        eventsManager.preventDefaultAction(event);
        eventsManager.cancelBubbling(event);
        dropHideTimeout = setTimeout(hideDropArea, 50);
    };
    var hideDropArea = function() {
        componentElement.classList.remove('feedback_dragged');
    };
    var showDropArea = function() {
        componentElement.classList.add('feedback_dragged');
    };
    var dropAreaDragEnterHandler = function(event) {
        eventsManager.preventDefaultAction(event);
        eventsManager.cancelBubbling(event);
        clearTimeout(dropHideTimeout);
        showDropArea();
    };
    var importFilesInfo = function(files) {
        fileInputElement.files = files;
        eventsManager.fireEvent(fileInputElement, 'change');
    };
    init();
};