window.zxMapLogics = new function() {
    var components = [];
    var mapsData;
    var initComponents = function() {
        if (typeof L !== 'undefined') {
            var elements = _('.zxmap');
            for (var i = 0; i < elements.length; i++) {
                components.push(new ZxMap(elements[i]));
            }
        }
    };
    var initLogics = function() {
        if (typeof window.mapsData !== 'undefined') {
            mapsData = window.mapsData;
        }
    };
    this.getData = function(id) {
        if (typeof mapsData[id] !== 'undefined') {
            return mapsData[id];
        }
        return false;
    };
    window.controller.addListener('initLogics', initLogics);
    window.controller.addListener('initDom', initComponents);
};