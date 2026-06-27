(function () {
    'use strict';

    const renderedWidgets = [];

    function getProvider() {
        return window.CodenitCaptchaData && window.CodenitCaptchaData.provider ? window.CodenitCaptchaData.provider : 'recaptcha';
    }

    function getSiteKey(element) {
        return element.getAttribute('data-sitekey') || (window.CodenitCaptchaData ? window.CodenitCaptchaData.siteKey : '');
    }

    function renderRecaptcha(element) {
        if (!element || element.dataset.codenitcaptchaRendered === '1' || typeof window.grecaptcha === 'undefined') {
            return;
        }

        const key = getSiteKey(element);
        if (!key) {
            return;
        }

        const params = { sitekey: key };
        const callback = element.getAttribute('data-callback');
        const expiredCallback = element.getAttribute('data-expired-callback');

        if (callback && typeof window[callback] === 'function') {
            params.callback = window[callback];
        }

        if (expiredCallback && typeof window[expiredCallback] === 'function') {
            params['expired-callback'] = window[expiredCallback];
        }

        try {
            const widgetId = window.grecaptcha.render(element, params);
            element.dataset.codenitcaptchaRendered = '1';
            renderedWidgets.push({ provider: 'recaptcha', id: widgetId });
        } catch (error) {}
    }

    function renderTurnstile(element) {
        if (!element || element.dataset.codenitcaptchaRendered === '1' || typeof window.turnstile === 'undefined') {
            return;
        }

        const key = getSiteKey(element);
        if (!key) {
            return;
        }

        try {
            const widgetId = window.turnstile.render(element, { sitekey: key });
            element.dataset.codenitcaptchaRendered = '1';
            renderedWidgets.push({ provider: 'turnstile', id: widgetId });
        } catch (error) {}
    }

    window.CodenitCaptchaRender = function () {
        const provider = getProvider();
        const selector = provider === 'turnstile'
            ? '.codenitcaptcha-turnstile, .cf-turnstile'
            : '.codenitcaptcha-recaptcha, .wpcf7-codenit_recaptcha';

        document.querySelectorAll(selector).forEach(function (element) {
            if (provider === 'turnstile') {
                renderTurnstile(element);
            } else {
                renderRecaptcha(element);
            }
        });
    };

    window.recaptchaCallback = function () {
        window.CodenitCaptchaRender();
    };

    window.turnstileCallback = function () {
        window.CodenitCaptchaRender();
    };

    document.addEventListener('wpcf7submit', function (event) {
        switch (event.detail.status) {
            case 'spam':
            case 'mail_sent':
            case 'mail_failed':
                renderedWidgets.forEach(function (widget) {
                    if (widget.provider === 'turnstile' && typeof window.turnstile !== 'undefined') {
                        window.turnstile.reset(widget.id);
                    }
                    if (widget.provider === 'recaptcha' && typeof window.grecaptcha !== 'undefined') {
                        window.grecaptcha.reset(widget.id);
                    }
                });
                break;
        }
    }, false);
})();
