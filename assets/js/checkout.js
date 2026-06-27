(function ($) {
    'use strict';

    function renderCheckoutCaptcha() {
        if (typeof window.CodenitCaptchaRender === 'function') {
            window.CodenitCaptchaRender();
        }
    }

    $(document.body).on('updated_checkout updated_wc_div', function () {
        renderCheckoutCaptcha();
    });

    document.addEventListener('DOMContentLoaded', function () {
        renderCheckoutCaptcha();
    });
})(jQuery);
