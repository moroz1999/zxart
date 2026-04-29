window.pagerLogics = new function() {
    var self = this;
    this.getPager = function(parameters) {
        var pagerData = self.getPagerData(parameters);
        var pager = new PagerComponent(parameters.componentElement);
        pager.updateData(pagerData);
        return pager;
    };
    this.getPagerData = function(parameters) {
        if (typeof parameters.elementsOnPage == 'undefined' || !parameters.elementsOnPage) {
            parameters.elementsOnPage = 10;
        }
        if (typeof parameters.currentPage == 'undefined' || !parameters.currentPage) {
            parameters.currentPage = 0;
        }
        if (typeof parameters.parameterName == 'undefined' || !parameters.parameterName) {
            parameters.parameterName = 'page';
        }
        if (typeof parameters.visibleAmount == 'undefined' || !parameters.visibleAmount) {
            parameters.visibleAmount = 1;
        }
        return new PagerData(parameters.baseURL, parameters.elementsCount, parameters.elementsOnPage, parameters.currentPage, parameters.parameterName, parameters.visibleAmount, parameters.callBack);
    };
};
window.PagerData = function(baseURL, elementsCount, elementsOnPage, currentPage, parameterName, visibleAmount, callBack) {
    var self = this;

    this.nextPage = {};
    this.pagesList = [];
    this.previousPage = {};
    this.currentPage = 0;
    this.startElement = 0;
    this.pagesAmount = 0;
    this.callBack = null;

    var init = function() {
        self.pagesAmount = Math.ceil(elementsCount / elementsOnPage);
        self.currentPage = currentPage;
        self.callBack = callBack;

        if (self.currentPage > self.pagesAmount) {
            self.currentPage = self.pagesAmount;
        } else if (self.currentPage < 1) {
            self.currentPage = 1;
        }

        self.startElement = (self.currentPage - 1) * elementsOnPage;

        self.previousPage['active'] = false;
        self.previousPage['text'] = '';
        self.previousPage['URL'] = '';
        self.previousPage['selected'] = false;
        if (self.currentPage !== 1) {
            self.previousPage['number'] = self.currentPage - 1;
            self.previousPage['active'] = true;
            self.previousPage['URL'] = baseURL + 'page:' + (self.currentPage - 1) + '/';
        }
        self.nextPage['active'] = false;
        self.nextPage['text'] = '';
        self.nextPage['URL'] = '';
        self.nextPage['selected'] = false;
        if (self.currentPage !== self.pagesAmount) {
            self.nextPage['number'] = self.currentPage + 1;
            self.nextPage['active'] = true;
            self.nextPage['URL'] = baseURL + 'page:' + (self.currentPage + 1) + '/';
        }

        var start = self.currentPage - visibleAmount;
        var end = self.currentPage + visibleAmount;

        if (self.currentPage <= visibleAmount + 2) {
            end = visibleAmount * 2 + 3;
        }

        if (self.currentPage >= self.pagesAmount - visibleAmount - 2) {
            start = self.pagesAmount - visibleAmount * 2 - 2;
        }

        if (start < 1) {
            start = 1;
        }
        if (end > self.pagesAmount) {
            end = self.pagesAmount;
        }

        if (start > 1) {
            self.pagesList.push(createPageElement(1));
        }
        if (start > 2) {
            self.pagesList.push(createPageElement('...'));
        }

        for (var i = start; i <= end; i++) {
            self.pagesList.push(createPageElement(i));
        }

        if (end < self.pagesAmount - 1) {
            self.pagesList.push(createPageElement('...'));
        }
        if (end < self.pagesAmount) {
            self.pagesList.push(createPageElement(self.pagesAmount));
        }
    };
    var createPageElement = function(number) {
        var element = {};
        if (!isNaN(number)) {
            element['text'] = number;
            element['number'] = number;
            element['active'] = true;
            element['URL'] = baseURL + 'page:' + number + '/';
            element['selected'] = element['number'] === self.currentPage;
        } else {
            element['text'] = '...';
            element['number'] = false;
            element['active'] = false;
            element['URL'] = false;
            element['selected'] = false;
        }
        return element;
    };
    init();
};