window.storageInterface = new function() {
    this.setValue = function(name, value) {
        storageCacheObject[name] = value;
        saveStorage();
    };
    this.getValue = function(name) {
        var value = false;
        if (typeof storageCacheObject[name] !== 'undefined') {
            value = storageCacheObject[name];
        }
        return value;
    };

    var init = function() {
        if (typeof window.localStorage !== 'undefined') {
            try {
                if (window.localStorage) {
                    storageType = 'localStorage';
                }
            } catch (e) {
            }
        } else {
            domElement = document.createElement('link');
            if (domElement.addBehavior) {
                domElement.style.behavior = 'url(#default#userData)';
                document.getElementsByTagName('head')[0].appendChild(domElement);

                storageType = 'userData';
            } else {
                domElement = null;
                return;
            }
        }

        loadStorage();
    };

    var loadStorage = function() {
        var storageString = false;
        if (storageType == 'userData') {
            try {
                domElement.load('storageInterfaceData');
                if (domElement.getAttribute('storageString')) {
                    storageString = domElement.getAttribute('storageString');
                }
            } catch (e) {
            }
        } else if (storageType == 'localStorage') {
            storageString = window.localStorage.getItem('storageString');
        }
        if (storageString) {
            try {
                storageCacheObject = json_decode(String(storageString));
            } catch (e) {
            }
            ;
        }
        if (!storageCacheObject) {
            storageCacheObject = {};
        }
    };
    var saveStorage = function() {

        try {
            var storageString = json_encode(storageCacheObject);

            if (storageType == 'userData') {
                domElement.setAttribute('storageString', storageString);
                domElement.save('storageInterfaceData');
            } else if (storageType == 'localStorage') {
                window.localStorage.setItem('storageString', storageString);
            }
        } catch (e) {
        }
        ;
    };
    var json_encode = Object.toJSON || (window.JSON && (JSON.encode || JSON.stringify));
    var json_decode = (window.JSON && (JSON.decode || JSON.parse)) || function(str) {
        return String(str).evalJSON();
    };

    var self = this;

    var storageCacheObject = {};
    var domElement = null;
    var storageService = null;
    var storageType = null;

    init();
};