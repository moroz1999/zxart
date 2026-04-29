window.ordersLogics = new function() {
    let ordersIndex = {};
    const init = function() {
        if (typeof window.orders !== 'undefined') {
            for (let i = 0; i < window.orders.length; i++) {
                let order = new Order(window.orders[i]);
                ordersIndex[order.getId()] = order;
            }
        }
    };

    this.getOrder = function(id) {
        if (typeof ordersIndex[id] !== 'undefined') {
            return ordersIndex[id];
        }
        return false;
    };

    controller.addListener('initLogics', init);
};

window.Order = function(data) {
    let id;
    let currency;
    let vatAmount;
    let products = [];
    let revenue;
    let shippingPrice;
    let coupon = '';
    let invoiceNumber;
    let discounts;
    const self = this;
    const init = function() {
        self.importData(data);
    };

    this.getId = function() {
        return id;
    };

    this.getInvoiceNumber = function() {
        return invoiceNumber;
    };

    this.getCurrency = function() {
        return currency;
    };

    this.getPriceWithoutVat = function() {
        let price = parseFloat(revenue) - parseFloat(vatAmount);
        return price.toFixed(2);
    };

    this.getVat = function() {
        return vatAmount;
    };

    this.getShippingPrice = function() {
        return shippingPrice;
    };

    this.getPromoCode = function() {
        for (let i = 0; i < discounts.length; i++) {
            coupon += discounts[i]['title'] + ' ';
        }
        return coupon;

    };

    this.getProducts = function() {
        return products;
    };

    this.getCoupon = function() {
        let coupon;
        if (discounts) {
            for (let i = 0; i < discounts.length; i++) {
                if (discounts.length === 1) {
                    coupon = discounts[i].title + ' (' + discounts[i].id + ')';
                }
            }
        }
        return coupon;
    };

    this.importData = function() {
        id = data.id;
        invoiceNumber = data.invoiceNumber;
        currency = data.currency;
        vatAmount = formatPrice(data.vatAmount);
        revenue = formatPrice(data.totalPrice);
        shippingPrice = formatPrice(data.deliveryPrice);
        discounts = formatPrice(data.discounts);
        products = data.products;
    };

    const formatPrice = function(price) {
        price = price.toString();
        return price.replace(/\s+/g, '');
    };

    init();
};