window.emailConversion = new function() {
    var init = function() {
        var elements = _('a[href^="mailto:"]');
        for (var i = 0; i < elements.length; i++) {
            elements[i].addEventListener('click', function(e) {
                var targetInnerHtml = e.target.innerHTML;
                tracking.emailTracking(targetInnerHtml);
            });

        }
    };
    controller.addListener('initDom', init);
};