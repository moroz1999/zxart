window.PrivilegesFormComponent = function(componentElement) {
    var holderForm;
    var form;
    var jsonInput;
    var init = function() {
        if (holderForm = componentElement.querySelector('.privilegesform_holder')) {
            if (form = componentElement.querySelector('.privilegesform_form')) {
                if (jsonInput = componentElement.querySelector('.privileges_json_input')) {
                    form.addEventListener('submit', submitHandler);
                }
            }
        }
    };
    var submitHandler = function() {
        var data = new FormData(holderForm);

        const entries = data.entries();
        const result = {};
        var next;
        var pair;
        while ((next = entries.next()) && next.done === false) {
            pair = next.value;
            result[pair[0]] = pair[1];
        }

        jsonInput.value = JSON.stringify(result);
    };
    init();
};