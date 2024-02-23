window.PurchaseButtonComponent = function (componentElement) {
    const init = function () {
        componentElement.addEventListener('click', salesClick);
    };
    const salesClick = function () {
        if (typeof ym !== 'undefined') {
            ym(94686067, 'reachGoal', 'open-cart-link', {}, () => window.open(this.model.externalLink));
        }
    }
    init();
};