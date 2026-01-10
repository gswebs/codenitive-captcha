=== Codenitive CAPTCHA Security ===
Contributors: gswebdev
Tags: google recaptcha, wordpress captcha, woocommerce security, Contact form 7 (cf7)
Requires at least: 5.6
Tested up to: 6.8.2
Requires PHP: 7.4
Stable tag: 1.0.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Protect your WordPress and WooCommerce login, registration, and checkout Contact form 7 (cf7) forms with lightweight Google reCAPTCHA v2.

== Description ==

Enhance your websites security by integrating CAPTCHA verification into essential WordPress WooCommerce and Contact form 7 forms. Google reCAPTCHA By Codenitive helps prevent spam, bot abuse, and unauthorized access by adding **Google reCAPTCHA** (v2) to critical forms:

* WordPress Login
* Registration
* Lost Password
* WooCommerce Login, Registration, Checkout
* Comments form
* Contact form 7 (cf7)

== Features ==

1. Add Google reCAPTCHA (v2) to:
  * WordPress Login, Registration, Lost Password, Comment form
  * WooCommerce Login, Registration, Checkout and Product Comment form
  * Contact form 7 (cf7)
1. Hide captcha for login users
1. Prevent spam, bots, and brute-force attacks
1. Easy configuration via plugin settings panel
1. Compatible with most themes and caching plugins
1. Tested with the latest WordPress and WooCommerce versions

== Installation ==

1. Upload the plugin folder `codenitive-captcha` to `/wp-content/plugins`, or install it via the Plugins screen in WordPress.
1. Activate the plugin through the **Plugins** menu.
1. Go to **Settings → Codenitive Captcha** to configure your Google reCAPTCHA site and secret keys.
1. Choose which forms to protect and which reCAPTCHA type to use (v2 Checkbox).
1. Save your settings.

== Usage ==

After activating the plugin:

1. Go to **Settings → Codenitive Captcha**
1. Paste your **Google Site Key** and **Secret Key**
1. Enable reCAPTCHA on forms you want to protect
1. Save settings — reCAPTCHA will now appear on your selected forms

== Privacy ==

This plugin connects to the external service Google reCAPTCHA to help prevent spam and abuse on your site.

== External Services Used ==

This plugin uses the following third-party service:

* Google reCAPTCHA v2
- **Purpose**: Prevent spam and automated abuse during form submissions (e.g. login, registration, checkout).
- **Data Sent**: When a user submits a protected form, their browser data (such as IP address, user-agent, mouse movements, and interaction behavior) may be sent to Google to determine if the user is human.
- **When Sent**: Only when a user interacts with a reCAPTCHA-protected form on the site.
- **Service Provider**: Google LLC
- **Terms of Service**: https://policies.google.com/terms
- **Privacy Policy**: https://policies.google.com/privacy

== Frequently Asked Questions ==

= Which reCAPTCHA versions are supported? =

Google reCAPTCHA v2 (Checkbox).

= Does this plugin work with WooCommerce? =

Yes! It supports login, registration, and checkout forms in WooCommerce.

= Does this plugin work with Contact form 7? =

Yes,

1. Go to **Settings → Codenitive Captcha**
1. Open the Options tab
1. Enable the Contact Form 7 checkbox
1. and add the shortcode [codenit_recaptcha] in the contact form 7
1. Save settings — reCAPTCHA will now appear on your form

= Can I customize where CAPTCHA appears? =

Yes, you can enable or disable CAPTCHA per form via the settings page.
Go to **Settings → Codenitive Captcha** to configure your Google reCAPTCHA site and secret keys.

= What if reCAPTCHA does not show? =

Make sure you've entered valid **site key** and **secret key** from the Google reCAPTCHA admin panel, and that the form you’re testing is enabled in settings.

= Where can I generate my reCAPTCHA Site and Secret Keys? =

<a href="https://www.google.com/recaptcha/admin/create" target="_blank">Click here to get the Site and Secret Keys</a>

== Coming Next ==
- Google reCAPTCHA v3 support

== Screenshots ==
1. Plugin settings page in WordPress dashboard
2. Add the reCAPTCHA site and secret keys.
3. CAPTCHA enabled on WordPress and WooCommerce Forms

== Changelog ==

= 1.0.0 =
* Initial release.
* Google reCAPTCHA v2.
* Integrates with WordPress and WooCommerce forms.

= 1.0.2 =
* Rename plugin to reCAPTCHA By Codenitive Hello

= 1.0.3 =
* Rename plugin to Codenitive CAPTCHA Security

= 1.0.4 =
* Add reCAPTCHA security for Contact form 7 (cf7)

= 1.0.5 =
* Fix login captcha

== Upgrade Notice ==

= 1.0.0 =
First release of Codenitive Captcha – secure your WordPress site with Google reCAPTCHA.

= 1.0.2 =
* Rename plugin to reCAPTCHA By Codenitive 

= 1.0.3 =
* Rename plugin to Codenitive CAPTCHA Security

= 1.0.4 =
* Add reCAPTCHA security for Contact form 7 (cf7)

= 1.0.5 =
* Fix login captcha

== Feedback ==

If you like this plugin, please [leave us a 5-star review](https://wordpress.org/support/plugin/codenitive-captcha/reviews/#new-post).  
It helps us improve and grow!

Need help or have a feature request? Use the [Support Forum](https://wordpress.org/support/plugin/codenitive-captcha/).