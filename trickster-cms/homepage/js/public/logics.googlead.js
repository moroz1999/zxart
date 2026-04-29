window.googleAd = new function() {
    var mainEvent;
    var feedbackEvent;
    var mailEvent;
    var buyEvent;

    var init = function() {
        if (typeof window.google !== 'undefined') {
            if (typeof (window.google.ad) === 'object' && window.google.ad !== null && typeof gtag !== 'undefined') {
                mainEvent = window.google.ad.mainId;
                feedbackEvent = window.google.ad.feedbackId;
                mailEvent = window.google.ad.emailId;
                buyEvent = window.google.ad.buyId;
            }
        }

    };

    this.sendEmailEvent = function() {
        if (mainEvent && mailEvent) {
            gtag('event', 'conversion', {'send_to': mainEvent + '/' + mailEvent});
        }
    };

    this.sendFeedbackEvent = function() {
        if (mainEvent && feedbackEvent) {
            gtag('event', 'conversion', {'send_to': mainEvent + '/' + feedbackEvent});
        }
    };

    this.sendBuyEvent = function(value, currency, orderId) {
        if (mainEvent && buyEvent) {
            if (value && currency && orderId) {
                var parameters = {};
                parameters.send_to = mainEvent + '/' + buyEvent;
                parameters.value = value;
                parameters.currency = currency;
                parameters.transaction_id = orderId;
                gtag('event', 'conversion', parameters);
            }
        }
    };

    controller.addListener('initLogics', init);
};