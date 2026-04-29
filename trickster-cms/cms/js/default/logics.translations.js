window.translationsLogics = new function() {
    var translationsList = {};
    var init = function() {
        if (typeof window.translations !== 'undefined') {
            translationsList = window.translations;
        }
    };
    this.get = function(name, parameters) {
        if (typeof translationsList[name] !== 'undefined') {
            var text = translationsList[name];
            if (typeof parameters !== 'undefined') {
                for (var i in parameters) {
                    if (parameters.hasOwnProperty(i)) {
                        text = text.replace('%' + i, parameters[i]);
                    }
                }
            }
            return text;
        } else {
            return '#' + name + '#';
        }
    };
    init();
};