window.googleAnalyticsLogics = new function() {
    let ecommerceEnabled = false;
    let gaCallBack;
    let callbackTimeout;
    let timeoutInterval = 250;

    const init = function() {
        checkEcommerce();
    };

    const checkEcommerce = function() {
        if (typeof window.google !== 'undefined') {
            if (typeof window.google.ecommerce !== 'undefined') {
                ecommerceEnabled = window.google.ecommerce.enabled;
            }
        }
    }

    const gaCallbackCaller = function() {
        clearTimeout(callbackTimeout);
        if (gaCallBack) {
            gaCallBack();
        }
    };

    const gaCallbackSetTimeout = function(callback) {
        gaCallBack = callback;
        callbackTimeout = setTimeout(gaCallbackCaller, timeoutInterval);
    };

    const formatPrice = function(price) {
        price = price.toString();
        return price.replace(/\s+/g, '');
    }

    this.productClickEvent = function(product, callBack) {
        gaCallbackSetTimeout(callBack);
        let list_name = '';
        if (window.currentElementTitle !== 'undefined') {
            list_name = window.currentElementTitle;
        }
        if (ecommerceEnabled && product) {
            gtag('event', 'select_content', {
                'content_type': 'product',
                'items': [
                    {
                        'id': product.id,
                        'name': product.name,
                        'brand': product.brand,
                        'category': product.category,
                        'list_name': list_name,
                        'price': formatPrice(product.price),
                    },
                ],
                'event_callback': gaCallbackCaller,
            });
        }

    };

    this.promoClickEvent = function(parameters, callBack) {
        // if (gaState && ecommerceEnabled && parameters) {
        // 	ga('ec:addProduct', {
        // 		'id': parameters.id,
        // 		'name': parameters.name,
        // 		'category': parameters.category,
        // 		'brand': parameters.brand
        // 	});
        // 	ga('ec:setAction', 'click', {list: 'promo_click'});
        // } else if (callBack) {
        // 	callBack();
        // }
    };

    this.addToBasketEvent = function(product) {
        if (ecommerceEnabled && product) {
            gtag('event', 'add_to_cart', {
                'items': [
                    {
                        'id': product.id,
                        'name': product.name,
                        'brand': product.brand,
                        'category': product.category,
                        'variant': product.variant,
                        'quantity': product.quantity,
                        'price': formatPrice(product.price),
                    },
                ],
            });
        }
    };

    this.checkoutEvent = function(parameters) {
        let products = [];
        if (ecommerceEnabled && parameters) {
            for (let i = 0; i < parameters.products.length; i++) {
                products.push({
                    'id': parameters.products[i].productId,
                    'name': parameters.products[i].title_dl,
                    'price': formatPrice(parameters.products[i].price),
                    'variant': parameters.products[i].variation_dl,
                    'quantity': parameters.products[i].amount,
                    'category': parameters.products[i].category_dl,
                });
            }
            gtag('event', 'begin_checkout', {
                'items': products,
                'coupon': '',
            });
        }
    };

    this.purchaseEvent = function(order) {
        if (ecommerceEnabled && order) {
            let products = [];
            for (let i = 0; i < order.products.length; i++) {
                products.push({
                    'id': order.products[i].id,
                    'name': order.products[i].title_ga,
                    'price': formatPrice(order.products[i].price),
                    'variant': order.products[i].variation_ga,
                    'quantity': order.products[i].amount,
                    'category': order.products[i].category_ga,
                });
            }
            gtag('event', 'purchase', {
                'transaction_id': order.id,
                'affiliation': order.affiliation,
                'value': order.revenue,
                'tax': order.tax,
                'shipping': order.shipping,
                'items': products,
            });
        }
    };

    this.removeFromBasketEvent = function(product) {
        if (ecommerceEnabled && product) {
            gtag('event', 'remove_from_cart', {
                'items': [
                    {
                        'id': product.id,
                        'name': product.name,
                        'brand': product.brand,
                        'category': product.category,
                        'variant': product.variant,
                        'quantity': product.quantity,
                        'price': formatPrice(product.price),
                    },
                ],
            });
        }

    };

    this.detailEvent = function(product) {
        if (ecommerceEnabled && product) {
            gtag('event', 'view_item', {
                'items': [
                    {
                        'id': product.id,
                        'name': product.name,
                        'category': product.category,
                        'price': formatPrice(product.price),
                    },
                ],
            });
        }
    };

    this.checkoutOptionsEvent = function(step, value) {
        checkEcommerce();
        if (ecommerceEnabled && value && step) {
            gtag('event', 'set_checkout_option', {
                'checkout_step': step,
                'checkout_option': value,
            });
        }
    };

    this.checkProgressEvent = function(step, parameters) {
        let products = Array();
        if (ecommerceEnabled && parameters) {
            for (let i = 0; i < parameters.products.length; i++) {
                products.push({
                    'id': parameters.products[i].productId,
                    'name': parameters.products[i].title_dl,
                    'price': formatPrice(parameters.products[i].price),
                    'variant': parameters.products[i].variation_dl,
                    'quantity': parameters.products[i].amount,
                    'category': parameters.products[i].category_dl,
                });
            }
            gtag('event', 'checkout_progress', {
                'items': products,
                'checkout_step': step,
            });
        }
    };

    this.feedbackEvent = function() {
        if (ecommerceEnabled) {
            gtag('event', 'Send', {
                'event_category': 'Forms',
                'event_label': 'Feedback',
            });
        }
    };

    this.impressionEvent = function(parameters) {
        let products = Array();
        let list_name = '';
        if (window.currentElementTitle !== 'undefined') {
            list_name = window.currentElementTitle;
        }
        if (ecommerceEnabled && parameters) {
            for (let i = 0; i < parameters.products.length; i++) {
                products.push({
                    'id': parameters.products[i].getId(),
                    'name': parameters.products[i].getName(),
                    'price': formatPrice(parameters.products[i].getPrice()),
                    'list_name': parameters.list_name,
                    'category': parameters.products[i].getCategory(),
                });
            }
            gtag('event', 'view_item_list', {
                'items': products,
            });
        }
    };

    controller.addListener('initLogics', init);
};