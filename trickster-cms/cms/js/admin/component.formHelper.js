window.FormHelperComponent = function(componentElement) {
    var self = this;
    var mark;
    var tip;

    var tipWidth;
    var tipHeight;

    var init = function() {
        mark = componentElement.querySelector('.form_helper_mark');
        tip = componentElement.querySelector('.form_helper_tip');
        if (tip && mark) {
            tipWidth = tip.offsetWidth;
            tipHeight = tip.offsetHeight;

            eventsManager.addHandler(mark, 'mouseover', onMouseOver);
            eventsManager.addHandler(mark, 'mouseout', onMouseOut);
        }
    };
    var onMouseOver = function() {
        tip.style.display = 'block';
        tip.style.visibility = 'visible';
        var rect = tip.getBoundingClientRect();

        findTipPlace(mark);

    };
    var onMouseOut = function() {
        tip.style.visibility = 'hidden';
        tip.setAttribute('style', 'display: none');
    };
    var findTipPlace = function(el) {
        var rect = el.getBoundingClientRect();
        var clientWidth = document.documentElement.clientWidth;
        var scrollPosY = self.getPageScroll().y;
        var tipRect = tip.getBoundingClientRect();

        if ((rect.right + 10 + tipWidth) > clientWidth) {
            var diff_x = tipRect.right - document.documentElement.clientWidth;
            tip.style.left = (tip.offsetLeft - diff_x - 25) + 'px';
            if ((scrollPosY > rect.top)) {
                tip.style.top = (tip.offsetTop + 35) + 'px';
            }
            if ((scrollPosY <= rect.top)) {
                tip.style.top = (tip.offsetTop - tipHeight - 5) + 'px';
            }
        } else {
            tip.style.top = (tip.offsetTop - tipHeight / 2 + 14) + 'px';
        }
    };

    init();
};
DomHelperMixin.call(FormHelperComponent.prototype);