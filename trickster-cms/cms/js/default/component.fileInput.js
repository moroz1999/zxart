function FileInputComponent(inputElement) {
    var componentElement;
    var fakeField;
    var fakeButton;
    var buttonText = '';

    var init = function() {

        if (window.translationsLogics.get('button.file_upload')) {
            buttonText = window.translationsLogics.get('button.file_upload');
        }

        createDom();
        synchronizeContent();
    };
    var createDom = function() {
        inputElement.style.display = 'none';

        componentElement = document.createElement('div');
        componentElement.className = 'file_input_container';

        fakeField = document.createElement('div');
        fakeField.className = 'input_component file_input_field'; // file name etc...
        fakeField.tabIndex = 0;
        componentElement.appendChild(fakeField);

        fakeButton = document.createElement('div');
        fakeButton.className = 'button file_input_button';
        componentElement.appendChild(fakeButton);

        var fakeButtonText = document.createElement('div');
        fakeButtonText.className = 'button_text';
        fakeButtonText.innerHTML = buttonText;
        fakeButton.appendChild(fakeButtonText);

        inputElement.parentNode.insertBefore(componentElement, inputElement);
        componentElement.appendChild(inputElement);

        // var inputParent = (inputElement.dataset.parent) ?
        //     inputElement.form.querySelector(inputElement.dataset.parent) :
        //     inputElement.parentNode;
        // componentElement.appendChild(inputElement);
        // var submit = (inputElement.dataset.firstChild) ?
        //     inputParent.querySelector(inputElement.dataset.firstChild) :
        //     inputParent.firstChild;
        // inputParent.insertBefore(componentElement, submit);

        eventsManager.addHandler(componentElement, 'click', clickHandler);
        eventsManager.addHandler(inputElement, 'change', synchronizeContent);
    };
    var synchronizeContent = function() {
        var fileSize = '';
        var fileName = '';
        var fileDescription = '<ol>';
        if (inputElement.value !== '') {
            for (var i = 0; i < inputElement.files.length; i++) {
                fileSize = (inputElement.files[i].size / 1024 / 1024).toFixed(4);// + " MB"
                fileName = inputElement.files[i].name;
                fileDescription += '<li>' + fileName + ' (' + fileSize + '  MB)</li>';
            }
            fakeField.innerHTML = fileDescription + "</ol>";
        }
    };
    var clickHandler = function() {
        if (typeof inputElement.click !== 'undefined') {
            inputElement.click();
        } else {
            eventsManager.fireEvent(inputElement, 'click');
        }
    };

    init();
}
