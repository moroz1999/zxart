function AjaxFormComponent(formElement, callCallback) {
    var formId;
    var formAction;
    var submitButton;
    var successMessageElement;
    var errorMessageElement;
    var hideOnSuccessElements;

    var init = function() {
        formId = formElement.elements['id'].value;
        formAction = formElement.elements['action'].value;
        successMessageElement = formElement.querySelector('.ajax_form_success_message');
        if (!successMessageElement) {
            successMessageElement = document.createElement('div');
            successMessageElement.className = 'ajax_form_success_message';
            formElement.insertBefore(successMessageElement, formElement.firstChild);
        }
        errorMessageElement = formElement.querySelector('.ajax_form_error_message');
        if (!errorMessageElement) {
            errorMessageElement = document.createElement('div');
            errorMessageElement.className = 'ajax_form_error_message';
            formElement.insertBefore(errorMessageElement, formElement.firstChild);
        }
        hideOnSuccessElements = formElement.querySelectorAll('.ajax_form_hide_on_success');

        if (submitButton = formElement.querySelector('.ajax_form_submit')) {
            eventsManager.addHandler(submitButton, 'click', submitForm);
            eventsManager.addHandler(formElement, 'submit', submitForm);
            eventsManager.addHandler(formElement, 'keydown', keyDownHandler);
        }
    };
    var keyDownHandler = function(event) {
        if (event.target && event.target.tagName.toLowerCase() != 'textarea') {
            if (event.keyCode == 13) {
                submitForm(event);
            }
        }
    };
    var submitForm = function(event) {
        eventsManager.preventDefaultAction(event);
        var check = Math.floor((new Date().getTime()) / 1000);
        var requestUrl = '/ajax/check:' + check + '/';
        if (formAction && formId) {
            var formData = new FormData(formElement);
            var request = new JsonRequest(requestUrl, receiveData, 'form' + formId + formAction, null, formData);
            request.send();
        }
        return false;
    };
    var receiveData = function(responseStatus, requestName, responseData) {
        var i, errorElement;
        var errorElementName;
        var errorElementParent;
        if (responseStatus == 'success') {
            var response = responseData['form' + formId + formAction];
            if (callCallback) {
                callCallback();
            }
            if (typeof response !== 'undefined') {
                //remove errors
                for (i = 0; i < formElement.elements.length; i++) {
                    if (errorElementName = formElement.elements[i].dataset.name || formElement.elements[i].name) {
                        errorElementParent = formElement.querySelector('[data-fieldname="' + errorElementName + '"]');
                        console.log(errorElementName)
                        if(errorElementParent) {
                            domHelper.removeClass(errorElementParent, 'form_error');
                        }
                    }
                    else {
                        domHelper.removeClass(formElement.elements[i].parentNode.parentNode, 'form_error');
                    }
                }

                if (typeof response.success_message !== 'undefined') {
                    if (typeof response.redirect !== 'undefined') {
                        document.location.href = response.redirect;
                    } else if (typeof response.reload !== 'undefined') {
                        document.location.href = document.location.href;
                    } else {
                        successMessageElement.innerHTML = response.success_message;
                        successMessageElement.style.display = 'block';

                        errorMessageElement.innerHTML = '';
                        errorMessageElement.style.display = 'none';

                        if (response.resetForm) {
                            resetForm();
                        }
                        for (i = 0; i < hideOnSuccessElements.length; i++) {
                            hideOnSuccessElements[i].style.display = 'none';
                        }
                    }
                } else {
                    //add errors
                    if (typeof response.errors !== 'undefined') {
                        for (i = 0; i < response.errors.length; i++) {
                            errorElementName = 'formData[' + formId + '][' + response.errors[i] + ']';
                            errorElement = formElement.elements[errorElementName];
                            if (!errorElement) {
                                errorElement = formElement.querySelector('[data-name="' + errorElementName + '"]');
                            }
                            errorElementParent = formElement.querySelector('[data-fieldname="' + errorElementName + '"]');

                            if(errorElementParent) {
                                domHelper.addClass(errorElementParent, 'form_error');
                            }
                            else {
                                 domHelper.addClass(errorElement.parentNode.parentNode, 'form_error');
                            }
                        }
                    }

                    if (typeof response.dynamicErrors !== 'undefined') {
                        for (i = 0; i < response.dynamicErrors.length; i++) {
                            errorElementName = 'formData[' + formId + '][dynamicFieldsData][' + response.dynamicErrors[i] + ']';
                            errorElement = formElement.elements[errorElementName];
                            errorElementParent = formElement.querySelector('[data-fieldname="' + errorElementName + '"]')
                            if(errorElementParent) {
                                domHelper.addClass(errorElementParent, 'form_error');
                            }
                            else {
                                domHelper.addClass(errorElement.parentNode.parentNode, 'form_error');
                            }
                        }
                    }

                    if (typeof response.error_message !== 'undefined' && response.error_message !== null) { // IE NULL fix
                        errorMessageElement.innerHTML = response.error_message;
                        errorMessageElement.style.display = 'block';
                    }
                }
            }
        }
    };
    var resetForm = function() {
        for (var i = formElement.elements.length; i--;) {
            var element = formElement.elements[i];
            if (element.name == 'id' || element.name == 'action')
                continue;
            var type = element.type.toLowerCase();
            switch (type) {
                case 'radio':
                case 'checkbox':
                    element.checked = false;
                    break;
                case 'select-one':
                case 'select-multi':
                    element.selectedIndex = -1;
                    break;
                default:
                    element.value = '';
                    break;
            }
        }
        if(formElement.querySelector('.file_input_field')) {
            formElement.querySelector('.file_input_field').innerHTML = '';
        }

    };
    init();
}