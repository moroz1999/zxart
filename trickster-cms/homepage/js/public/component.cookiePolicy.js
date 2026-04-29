window.CookiePolicy = function() {
    var cookieContainerElement, cookieButtonElement;

    var init = function() {
        if (window.settings && window.settings != undefined && window.settings.show_cookie_policy != undefined && window.settings.show_cookie_policy == '1') {
            var cookiePolicyAccepted = Cookies.get('cookiePolicyAccepted');
            if (!cookiePolicyAccepted) {
                showCookiePolicy();
            }
        }
    };

    var showCookiePolicy = function() {
        cookieContainerElement = document.createElement('div');
        domHelper.addClass(cookieContainerElement, 'cookie_policy_component');

        if (window.translationsLogics.get('cookiepolicy.intro') != '') {
            var cookieTextElement = document.createElement('span');
            cookieTextElement.innerHTML = window.translationsLogics.get('cookiepolicy.intro');
            domHelper.addClass(cookieTextElement, 'cookie_policy_text');
            cookieContainerElement.appendChild(cookieTextElement);
        }
        var cookieControlsElement = document.createElement('span');
        domHelper.addClass(cookieControlsElement, 'cookie_policy_controls');
        cookieContainerElement.appendChild(cookieControlsElement);

        if (window.translationsLogics.get('cookiepolicy.buttonText') != '') {
            cookieButtonElement = document.createElement('span');
            cookieButtonElement.innerHTML = window.translationsLogics.get('cookiepolicy.buttonText');
            domHelper.addClass(cookieButtonElement, 'cookie_policy_button button');
            cookieControlsElement.appendChild(cookieButtonElement);
            eventsManager.addHandler(cookieButtonElement, 'click', cookieButtonClickHandler);
        }

        if (window.translationsLogics.get('cookiepolicy.infoLinkText') != '') {
            var cookieInfoElement = document.createElement('a');
            cookieInfoElement.href = window.translationsLogics.get('cookiepolicy.infoLink');
            cookieInfoElement.target = '_blank';
            cookieInfoElement.innerHTML = window.translationsLogics.get('cookiepolicy.infoLinkText');
            domHelper.addClass(cookieInfoElement, 'cookie_policy_link');
            cookieControlsElement.appendChild(cookieInfoElement);
        }
        var body = _('body')[0];
        body.appendChild(cookieContainerElement);
    };

    var cookieButtonClickHandler = function() {
        cookieContainerElement.parentNode.removeChild(cookieContainerElement);
        Cookies.set('cookiePolicyAccepted', true, {expires: Infinity});
    };

    controller.addListener('initDom', init);
};
window.CookiePolicy();