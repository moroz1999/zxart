var $smShareElements = document.querySelectorAll('a.sm_share');
if ($smShareElements.length) {
    [].forEach.call($smShareElements, function(smShareElement, i) {
        smShare(smShareElement,i);
    });
}

function smShare(el, i) {
    el.addEventListener("click", function(e){
        e.preventDefault();
        e.stopPropagation();
        var $link   = this;
        var href    = $link.href;
        var smTarget = $link.dataset.smTarget;
        var smTargets = {
            facebook : { width : 600, height : 300 },
            twitter  : { width : 600, height : 450 },
            google   : { width : 515, height : 490 },
            linkedin : { width : 600, height : 475 }
        };

        var popup = function(smTarget){
            var options = 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,';
            window.open(href, '', options+'height='+smTargets[smTarget].height+',width='+smTargets[smTarget].width);
        }

        popup(smTarget);
    }, false);
}
