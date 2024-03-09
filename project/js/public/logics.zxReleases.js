window.zxReleasesLogics = new function () {
    var initComponents = function () {
        var elements, i;
        elements = _('.zxrelease_details');
        for (i = 0; i < elements.length; i++) {
            new ZxReleaseDetailsComponent(elements[i]);
        }
    };
    this.getReleaseData = function (id) {
        if (window.releasesInfo && window.releasesInfo[id]) {
            return window.releasesInfo[id];
        }
        return null;
    }

    window.controller.addListener('initDom', initComponents);
};