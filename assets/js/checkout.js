let captchaRendered = false;
let site_key = codenitcaptcha_captcha_obj.sitekey;
function renderCaptchaOnCheckout() {
    const wrapper = document.querySelector('#wccn-captcha-box');
    if (!wrapper || typeof grecaptcha === 'undefined') return;

    // Remove previous .g-recaptcha to avoid double render
    const oldRecaptcha = wrapper.querySelector('.g-recaptcha');
    if (oldRecaptcha) oldRecaptcha.remove();

    // Add new one
    const newRecaptcha = document.createElement('div');
    newRecaptcha.className = 'g-recaptcha';
    wrapper.appendChild(newRecaptcha);

    recaptchaWidgetId = grecaptcha.render(newRecaptcha, {
        sitekey: site_key
    });
}

function onRecaptchaApiLoad() {
    renderCaptchaOnCheckout();

    jQuery(document.body).on('updated_checkout updated_wc_div', function () {
        renderCaptchaOnCheckout();
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const script = document.createElement('script');
    script.src = "https://www.google.com/recaptcha/api.js?onload=onRecaptchaApiLoad&render=explicit";
    script.async = true;
    script.defer = true;
    document.getElementById('recaptcha-script-placeholder').appendChild(script);
});