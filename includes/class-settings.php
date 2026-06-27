<?php
namespace codenitcaptcha\includes\settings;
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CODENITCA_Captcha_Settings {
    public static function init() {
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_menu', array(__CLASS__, 'add_settings_page'));
        add_filter( 'plugin_action_links_' . CODENITCAPTCHA_PLUGIN_BASENAME, [__CLASS__, 'add_action_links'] );
    }

    public static function add_settings_page() {
        add_options_page(
            'CodeNitive CAPTCHA Settings',
            'Codenitive CAPTCHA',
            'manage_options',
            'codenitive-captcha-settings',
            array(__CLASS__, 'render_settings_page')
        );
    }

    public static function check_active_woo(): bool {
        $return = false;
        $active_plugins = get_option( 'active_plugins', array() );
        // Check if WooCommerce is active
        if ( in_array( 'woocommerce/woocommerce.php', $active_plugins ) ) {
            $return = true;
        }

        return $return;
    }

    public static function check_active_cf7(): bool {
        $return = false;
        $active_plugins = get_option( 'active_plugins', array() );
        // Check if WooCommerce is active
        if ( in_array( 'contact-form-7/wp-contact-form-7.php', $active_plugins ) ) {
            $return = true;
        }

        return $return;
    }

    public static function add_action_links ( $links ) {
        $mylinks = array(
            '<a href="' . admin_url( 'options-general.php?page=codenitive-captcha-settings' ) . '" target="_blank">Settings</a>',
        );
        return array_merge( $links, $mylinks );
    }

    public static function render_settings_page() {

        $active_tab = 'googlerecaptcha';
        $nonce = wp_create_nonce( 'codenitcaptcha_tabs_nonce' );

        if ( isset( $_GET['tab'], $_GET['_wpnonce'] ) &&
            wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'codenitcaptcha_tabs_nonce' )) {
            $active_tab = sanitize_key( wp_unslash( $_GET['tab'] ) );
        }

        ?>
        <div class="wrap">
            <h1>CODENITIVE CAPTCHA Settings</h1>

            <h2 class="nav-tab-wrapper">
                <a href="?page=codenitive-captcha-settings&tab=googlerecaptcha&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="nav-tab <?php echo $active_tab == 'googlerecaptcha' ? 'nav-tab-active' : ''; ?>">Google reCaptcha</a>
                <a href="?page=codenitive-captcha-settings&tab=turnstile&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="nav-tab <?php echo $active_tab == 'turnstile' ? 'nav-tab-active' : ''; ?>">Cloudflare Turnstile</a>
                <a href="?page=codenitive-captcha-settings&tab=options&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="nav-tab <?php echo $active_tab == 'options' ? 'nav-tab-active' : ''; ?>">Options</a>
            </h2>
            <br>
            <form method="post" action="options.php">

                <?php
                if ($active_tab == 'googlerecaptcha') {
                    ?>
                    <a href="https://www.google.com/recaptcha/admin/create" target="_blank">Click here to get the Site and Secret Keys</a>
                    <br>
                    <?php
                    settings_fields('codenitcaptcha_googlekeys');
                    do_settings_sections('codenitcaptcha_googlekeys');
                } elseif ($active_tab == 'turnstile') {
                    ?>
                    <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank" rel="noopener noreferrer">Click here to create a Cloudflare Turnstile widget and get keys</a>
                    <p class="description">If Cloudflare Turnstile and Google reCAPTCHA are both enabled with valid keys, Turnstile is used first.</p>
                    <br>
                    <?php
                    settings_fields('codenitcaptcha_turnstilekeys');
                    do_settings_sections('codenitcaptcha_turnstilekeys');
                } elseif ($active_tab == 'options') {
                    settings_fields('codenitcaptcha_options');
                    do_settings_sections('codenitcaptcha_options');
                }
                ?>
                
                <?php submit_button(); ?>
            </form>
            
        </div>
        <?php
    }

    public static function register_settings() {

        add_settings_section('codenitcaptcha_googlekeys_section', '<h3>Google Captcha V2</h3><hr>', null, 'codenitcaptcha_googlekeys');
        add_settings_section('codenitcaptcha_turnstilekeys_section', '<h3>Cloudflare Turnstile</h3><hr>', null, 'codenitcaptcha_turnstilekeys');
        add_settings_section('codenitcaptcha_wp_options_section', '<h3>WordPress Options</h3><hr>', null, 'codenitcaptcha_options');
        
        if (self::check_active_cf7()) {
            add_settings_section('codenitcaptcha_wp_options_section', '<h3>Contact Form 7 reCaptcha Options</h3><hr>', null, 'codenitcaptcha_cf7_recaptcha');
        }

        if (self::check_active_woo()) {
            add_settings_section('codenitcaptcha_woo_options_section', '<h3>Woocommerce Options</h3><hr>', null, 'codenitcaptcha_options');
        }
        
        add_settings_section('codenitcaptcha_miscellaneous_section', '<h3>Miscellaneous</h3><hr>', null, 'codenitcaptcha_options');

        self::codenitcaptcha_register_field([
            'option_group' => 'codenitcaptcha_googlekeys',
            'option_name'  => 'codenitcaptcha_v2_status',
            'field_label'  => 'Enable V2',
            'field_type'   => 'checkbox',
            'page'         => 'codenitcaptcha_googlekeys',
            'section'      => 'codenitcaptcha_googlekeys_section',
        ]);

        self::codenitcaptcha_register_field([
            'option_group' => 'codenitcaptcha_googlekeys',
            'option_name'  => 'codenitcaptcha_site_key',
            'field_label'  => 'Site Key',
            'page'         => 'codenitcaptcha_googlekeys',
            'section'      => 'codenitcaptcha_googlekeys_section',
        ]);

        self::codenitcaptcha_register_field([
            'option_group' => 'codenitcaptcha_googlekeys',
            'option_name'  => 'codenitcaptcha_secret_key',
            'field_label'  => 'Secret Key',
            'page'         => 'codenitcaptcha_googlekeys',
            'section'      => 'codenitcaptcha_googlekeys_section',
        ]);


        self::codenitcaptcha_register_field([
            'option_group' => 'codenitcaptcha_turnstilekeys',
            'option_name'  => 'codenitcaptcha_turnstile_status',
            'field_label'  => 'Enable Turnstile',
            'field_type'   => 'checkbox',
            'page'         => 'codenitcaptcha_turnstilekeys',
            'section'      => 'codenitcaptcha_turnstilekeys_section',
        ]);

        self::codenitcaptcha_register_field([
            'option_group' => 'codenitcaptcha_turnstilekeys',
            'option_name'  => 'codenitcaptcha_turnstile_site_key',
            'field_label'  => 'Site Key',
            'page'         => 'codenitcaptcha_turnstilekeys',
            'section'      => 'codenitcaptcha_turnstilekeys_section',
        ]);

        self::codenitcaptcha_register_field([
            'option_group' => 'codenitcaptcha_turnstilekeys',
            'option_name'  => 'codenitcaptcha_turnstile_secret_key',
            'field_label'  => 'Secret Key',
            'page'         => 'codenitcaptcha_turnstilekeys',
            'section'      => 'codenitcaptcha_turnstilekeys_section',
        ]);

        if (self::check_active_woo()) {

            self::codenitcaptcha_register_field([
                'option_group' => 'codenitcaptcha_options',
                'option_name'  => 'codenitcaptcha_woo_login',
                'field_label'  => 'Login Form',
                'field_type'   => 'checkbox',
                'page'         => 'codenitcaptcha_options',
                'section'      => 'codenitcaptcha_woo_options_section',
            ]);

            self::codenitcaptcha_register_field([
                'option_group' => 'codenitcaptcha_options',
                'option_name'  => 'codenitcaptcha_woo_register',
                'field_label'  => 'Registration Form',
                'field_type'   => 'checkbox',
                'page'         => 'codenitcaptcha_options',
                'section'      => 'codenitcaptcha_woo_options_section',
            ]);
            
            self::codenitcaptcha_register_field([
                'option_group' => 'codenitcaptcha_options',
                'option_name'  => 'codenitcaptcha_woo_forgetpass',
                'field_label'  => 'Reset Password',
                'field_type'   => 'checkbox',
                'page'         => 'codenitcaptcha_options',
                'section'      => 'codenitcaptcha_woo_options_section',
            ]);

            self::codenitcaptcha_register_field([
                'option_group' => 'codenitcaptcha_options',
                'option_name'  => 'codenitcaptcha_woo_checkout',
                'field_label'  => 'Checkout',
                'field_type'   => 'checkbox',
                'page'         => 'codenitcaptcha_options',
                'section'      => 'codenitcaptcha_woo_options_section',
            ]);

            self::codenitcaptcha_register_field([
                'option_group' => 'codenitcaptcha_options',
                'option_name'  => 'codenitcaptcha_woo_comments',
                'field_label'  => 'Hide from Product Comment Form',
                'field_type'   => 'checkbox',
                'page'         => 'codenitcaptcha_options',
                'section'      => 'codenitcaptcha_woo_options_section',
                'description'  => 'Check to hide the captcha from products comment form.'
            ]);

        }

        self::codenitcaptcha_register_field([
            'option_group' => 'codenitcaptcha_options',
            'option_name'  => 'codenitcaptcha_wp_login',
            'field_label'  => 'Login Form',
            'field_type'   => 'checkbox',
            'page'         => 'codenitcaptcha_options',
            'section'      => 'codenitcaptcha_wp_options_section',
        ]);

        self::codenitcaptcha_register_field([
            'option_group' => 'codenitcaptcha_options',
            'option_name'  => 'codenitcaptcha_wp_register',
            'field_label'  => 'Registration Form',
            'field_type'   => 'checkbox',
            'page'         => 'codenitcaptcha_options',
            'section'      => 'codenitcaptcha_wp_options_section',
        ]);

        self::codenitcaptcha_register_field([
            'option_group' => 'codenitcaptcha_options',
            'option_name'  => 'codenitcaptcha_wp_forget_pass',
            'field_label'  => 'Reset Password',
            'field_type'   => 'checkbox',
            'page'         => 'codenitcaptcha_options',
            'section'      => 'codenitcaptcha_wp_options_section',
        ]);

        self::codenitcaptcha_register_field([
            'option_group' => 'codenitcaptcha_options',
            'option_name'  => 'codenitcaptcha_wp_comments',
            'field_label'  => 'Posts Comments',
            'field_type'   => 'checkbox',
            'page'         => 'codenitcaptcha_options',
            'section'      => 'codenitcaptcha_wp_options_section',
            'description'  => 'Check to enable the captcha in all post types comment forms including Woocommerce Products. To hide the captcha from Product Comment form check the `Hide from Product Comment Form` option inside the Woocommerce options.'
        ]);

        if (self::check_active_cf7()) {
            self::codenitcaptcha_register_field([
                'option_group' => 'codenitcaptcha_options',
                'option_name'  => 'codenitcaptcha_cf7_forms',
                'field_label'  => 'Contact Form 7',
                'field_type'   => 'checkbox',
                'page'         => 'codenitcaptcha_options',
                'section'      => 'codenitcaptcha_wp_options_section',
                'description'  => 'Check this option and add the [codenit_recaptcha] shortcode in the contact form 7.'
            ]);
        }

        self::codenitcaptcha_register_field([
            'option_group' => 'codenitcaptcha_options',
            'option_name'  => 'codenitcaptcha_hide_login',
            'field_label'  => 'Show for login users',
            'field_type'   => 'checkbox',
            'page'         => 'codenitcaptcha_options',
            'section'      => 'codenitcaptcha_miscellaneous_section',
            'description'  => 'This will show and hide the captcha from comments and checkout forms.'
        ]);


    }

    public static function codenitcaptcha_register_field($args) {
        $defaults = [
            'option_group'      => '',
            'option_name'       => '',
            'field_label'       => '',
            'field_type'        => 'text', // text | checkbox | select
            'callback'          => null,
            'page'              => '',
            'section'           => '',
            'choices'           => [],     // for select dropdown
            'sanitize_callback' => null,   // optional custom sanitizer
        ];

        $args = wp_parse_args($args, $defaults);

        // Set default sanitizers based on field_type if none provided
        if (empty($args['sanitize_callback'])) {
            switch ($args['field_type']) {
                case 'checkbox':
                    $args['type'] = 'string';
                    $args['sanitize_callback'] = function ($value) {
                        return $value === '1' ? '1' : '0';
                    };
                    break;

                case 'select':
                case 'text':
                default:
                    $args['type'] = 'string';
                    $args['sanitize_callback'] = 'sanitize_text_field';
                    break;
            }
        }

        // Register the setting with sanitization
        register_setting(
            $args['option_group'],
            $args['option_name'],
            [   
                'type' => $args['type'],
                'sanitize_callback' => $args['sanitize_callback'],
            ]
        );

        // Auto create a basic callback if none provided
        if (!$args['callback']) {
            $args['callback'] = function () use ($args) {
                $value = get_option($args['option_name'], '');

                switch ($args['field_type']) {
                    case 'checkbox':
                        ?>
                        <input type="checkbox" name="<?php echo esc_attr($args['option_name']); ?>" value="1" <?php checked(1, $value, true); ?> />
                        <p class="description"><?php echo isset($args['description']) ? esc_attr($args['description']) : ''; ?></p>
                        <?php
                        break;

                    case 'select':
                        ?>
                        <select name="<?php echo esc_attr($args['option_name']); ?>">
                            <?php foreach ($args['choices'] as $key => $label): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($key, $value); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php
                        break;

                    case 'text':
                    default:
                        ?>
                        <input type="text" name="<?php echo esc_attr($args['option_name']); ?>" value="<?php echo esc_attr($value); ?>" class="regular-text" />
                        <?php
                        break;
                }
            };
        }

        // Add the field
        add_settings_field(
            $args['option_name'],
            $args['field_label'],
            $args['callback'],
            $args['page'],
            $args['section']
        );
    }    

}
