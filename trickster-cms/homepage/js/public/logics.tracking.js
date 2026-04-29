window.tracking = {
    buyTracking: function(orderId) {
        var order = ordersLogics.getOrder(orderId);
        if (order) {
            googleAd.sendBuyEvent(order.getInvoiceNumber(), order.getCurrency(), orderId);
            var products = order.getProducts();
            var parameters = {};
            if (products) {
                parameters.id = order.getInvoiceNumber();
                parameters.affiliation = 'shop';
                parameters.revenue = order.getPriceWithoutVat();
                parameters.tax = order.getVat();
                parameters.shipping = order.getShippingPrice();
                // parameters.coupon = order.getPromoCode();
                parameters.products = products;

                googleAnalyticsLogics.purchaseEvent(parameters);
            }
        }
    },

    emailTracking: function(email) {
        googleAd.sendEmailEvent();
        trackingComponent.emailClickEvent(email);
    },

    feedbackTracking: function() {
        googleAd.sendFeedbackEvent();
        googleAnalyticsLogics.feedbackEvent();
    },

    promoClickTracking: function() {
        var product = productLogics.getProduct();
        var parameters = {};

        parameters.id = product.getId();
        parameters.name = product.getName();
        parameters.price = product.getPrice();
        parameters.position = product.getPosition();

        googleAnalyticsLogics.promoClickEvent();
    },

    productClickTracking: function(id, callback) {
        var product = productLogics.getProduct(id);
        var parameters = {};
        if (product) {
            parameters.id = product.getId();
            parameters.name = product.getName();
            parameters.brand = product.getBrand();
            parameters.category = product.getCategory();
            parameters.variant = product.getVariant();
            parameters.price = product.getPrice();
            parameters.quantity = product.getQuantity();
            parameters.coupon = product.getCoupon();
            parameters.position = product.getPosition();

            googleAnalyticsLogics.productClickEvent(parameters, callback);
        } else if (callback) {
            callback();
        }

    },

    addToBasketTracking: function(product, amount) {
        var parameters = {};
        if (product) {
            parameters.id = product.productId;
            parameters.name = product.title_dl;
            parameters.category = product.category_dl;
            parameters.brand = product.brand;
            parameters.variant = '';
            for (var i = 0; i < product.variation_dl.length; i++) {
                parameters.variant += product.variation_dl[i] + ' ';
            }
            parameters.price = product.price;
            parameters.quantity = amount;

            googleAnalyticsLogics.addToBasketEvent(parameters);
        }
    },

    checkoutTracking: function(products) {
        var parameters = {};
        if (products) {
            parameters.products = products;
        }

        googleAnalyticsLogics.checkoutEvent(parameters);
    },

    checkoutProgressTracking: function(step, products) {
        var parameters = {};
        if (products) {
            parameters.products = products;
        }
        googleAnalyticsLogics.checkProgressEvent(step, parameters);
    },

    removeFromBasket: function(product) {
        var parameters = {};
        if (product) {
            parameters.id = product.productId;
            parameters.name = product.title_dl;
            parameters.price = product.price;
            parameters.quantity = product.amount;
            parameters.variant = product.variation_dl;
            googleAnalyticsLogics.removeFromBasketEvent(parameters);
        }
    },

    checkoutOptionsTracking: function(step, value) {
        if (value && step) {
            googleAnalyticsLogics.checkoutOptionsEvent(step, value);
        }
    },

    detailTracking: function(product) {
        var parameters = {};
        if (product) {
            parameters.id = product.id;
            parameters.name = product.name;
            parameters.category = product.category;
            parameters.price = product.price;

            googleAnalyticsLogics.detailEvent(parameters);
        }
    },

    impressionTracking: function(listName, products) {
        if (products) {
            var parameters = {};
            parameters.list_name = listName;
            parameters.products = products;
            googleAnalyticsLogics.impressionEvent(parameters);
        }
    },
};