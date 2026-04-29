window.TableComponent = function(tableComponent) {
    var self = this;
    var bottomElement;
    var pagerComponent;

    var init = function() {
        bottomElement = document.querySelector('.content_list_bottom');
        if (tableComponent) {
            var element = document.querySelector('.pager_block');
            if (element) {
                pagerComponent = new PagerComponent(element, tableComponent);

                window.addEventListener('scroll', updatePager);
                window.addEventListener('load', updatePager);
            }
        }
    };

    var updatePager = function() {
        if (self.isOnScreen(tableComponent) && !self.isOnScreen(bottomElement)) {
            pagerComponent.setSticky(true);
        } else {
            pagerComponent.setSticky(false);
        }
    };

    init();
};

DomHelperMixin.call(TableComponent.prototype);