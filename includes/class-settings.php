<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class JMB_Captcha_Settings {
    public static function init() {
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_menu', array(__CLASS__, 'add_settings_page'));
        add_filter( 'plugin_action_links_' . JMB_CAPTCHA_PLUGIN_BASENAME, [__CLASS__, 'add_action_links'] );
    }

    public static function add_settings_page() {
        add_options_page(
            'Codenitive CAPTCHA Settings',
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

    public static function add_action_links ( $links ) {
        $mylinks = array(
            '<a href="' . admin_url( 'options-general.php?page=codenitive-captcha-settings' ) . '" target="_blank">Settings</a>',
        );
        return array_merge( $links, $mylinks );
    }

    public static function render_settings_page() {

        $active_tab = 'googlerecaptcha';
        $nonce = wp_create_nonce( 'jmb_captcha_tabs_nonce' );

        if ( isset( $_GET['tab'], $_GET['_wpnonce'] ) &&
            wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'jmb_captcha_tabs_nonce' )) {
            $active_tab = sanitize_key( wp_unslash( $_GET['tab'] ) );
        }

        ?>
        <div class="wrap">
            <h1>JMB CAPTCHA Settings</h1>

            <h2 class="nav-tab-wrapper">
                <a href="?page=codenitive-captcha-settings&tab=googlerecaptcha&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="nav-tab <?php echo $active_tab == 'googlerecaptcha' ? 'nav-tab-active' : ''; ?>">Google reCaptcha</a>
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
                    settings_fields('jmb_captcha_googlekeys');
                    do_settings_sections('jmb_captcha_googlekeys');
                } elseif ($active_tab == 'options') {
                    settings_fields('jmb_captcha_options');
                    do_settings_sections('jmb_captcha_options');
                }
                ?>
                
                <?php submit_button(); ?>
            </form>
            <style>
                h3{
                    color: #135e96;
                }
            </style>
            
        </div>
        <?php
    }

    public static function register_settings() {

        add_settings_section('jmb_captcha_googlekeys_section', '<h3>Google Captcha V2</h3><hr>', null, 'jmb_captcha_googlekeys');
        add_settings_section('jmb_captcha_wp_options_section', '<h3>WordPress Options</h3><hr>', null, 'jmb_captcha_options');
        
        if (self::check_active_woo()) {
            add_settings_section('jmb_captcha_woo_options_section', '<h3>Woocommerce Options</h3><hr>', null, 'jmb_captcha_options');
        }
        
        add_settings_section('jmb_captcha_miscellaneous_section', '<h3>Miscellaneous</h3><hr>', null, 'jmb_captcha_options');

        self::jmb_register_field([
            'option_group' => 'jmb_captcha_googlekeys',
            'option_name'  => 'jmb_captcha_v2_status',
            'field_label'  => 'Enable V2',
            'field_type'   => 'checkbox',
            'page'         => 'jmb_captcha_googlekeys',
            'section'      => 'jmb_captcha_googlekeys_section',
        ]);

        self::jmb_register_field([
            'option_group' => 'jmb_captcha_googlekeys',
            'option_name'  => 'jmb_captcha_site_key',
            'field_label'  => 'Site Key',
            'page'         => 'jmb_captcha_googlekeys',
            'section'      => 'jmb_captcha_googlekeys_section',
        ]);

        self::jmb_register_field([
            'option_group' => 'jmb_captcha_googlekeys',
            'option_name'  => 'jmb_captcha_secret_key',
            'field_label'  => 'Secret Key',
            'page'         => 'jmb_captcha_googlekeys',
            'section'      => 'jmb_captcha_googlekeys_section',
        ]);

        if (self::check_active_woo()) {

            self::jmb_register_field([
                'option_group' => 'jmb_captcha_options',
                'option_name'  => 'jmb_captcha_woo_login',
                'field_label'  => 'Login Form',
                'field_type'   => 'checkbox',
                'page'         => 'jmb_captcha_options',
                'section'      => 'jmb_captcha_woo_options_section',
            ]);

            self::jmb_register_field([
                'option_group' => 'jmb_captcha_options',
                'option_name'  => 'jmb_captcha_woo_register',
                'field_label'  => 'Registration Form',
                'field_type'   => 'checkbox',
                'page'         => 'jmb_captcha_options',
                'section'      => 'jmb_captcha_woo_options_section',
            ]);
            
            self::jmb_register_field([
                'option_group' => 'jmb_captcha_options',
                'option_name'  => 'jmb_captcha_woo_forgetpass',
                'field_label'  => 'Reset Password',
                'field_type'   => 'checkbox',
                'page'         => 'jmb_captcha_options',
                'section'      => 'jmb_captcha_woo_options_section',
            ]);

            self::jmb_register_field([
                'option_group' => 'jmb_captcha_options',
                'option_name'  => 'jmb_captcha_woo_checkout',
                'field_label'  => 'Checkout',
                'field_type'   => 'checkbox',
                'page'         => 'jmb_captcha_options',
                'section'      => 'jmb_captcha_woo_options_section',
            ]);

            self::jmb_register_field([
                'option_group' => 'jmb_captcha_options',
                'option_name'  => 'jmb_captcha_woo_comments',
                'field_label'  => 'Hide from Product Comment Form',
                'field_type'   => 'checkbox',
                'page'         => 'jmb_captcha_options',
                'section'      => 'jmb_captcha_woo_options_section',
                'description'  => 'Check to hide the captcha from products comment form.'
            ]);

        }

        self::jmb_register_field([
            'option_group' => 'jmb_captcha_options',
            'option_name'  => 'jmb_captcha_wp_login',
            'field_label'  => 'Login Form',
            'field_type'   => 'checkbox',
            'page'         => 'jmb_captcha_options',
            'section'      => 'jmb_captcha_wp_options_section',
        ]);

        self::jmb_register_field([
            'option_group' => 'jmb_captcha_options',
            'option_name'  => 'jmb_captcha_wp_register',
            'field_label'  => 'Registration Form',
            'field_type'   => 'checkbox',
            'page'         => 'jmb_captcha_options',
            'section'      => 'jmb_captcha_wp_options_section',
        ]);

        self::jmb_register_field([
            'option_group' => 'jmb_captcha_options',
            'option_name'  => 'jmb_captcha_wp_forget_pass',
            'field_label'  => 'Reset Password',
            'field_type'   => 'checkbox',
            'page'         => 'jmb_captcha_options',
            'section'      => 'jmb_captcha_wp_options_section',
        ]);

        self::jmb_register_field([
            'option_group' => 'jmb_captcha_options',
            'option_name'  => 'jmb_captcha_wp_comments',
            'field_label'  => 'Posts Comments',
            'field_type'   => 'checkbox',
            'page'         => 'jmb_captcha_options',
            'section'      => 'jmb_captcha_wp_options_section',
            'description'  => 'Check to enable the captcha in all post types comment forms including Woocommerce Products. To hide the captcha from Product Comment form check the `Hide from Product Comment Form` option inside the Woocommerce options.'
        ]);

        self::jmb_register_field([
            'option_group' => 'jmb_captcha_options',
            'option_name'  => 'jmb_captcha_hide_login',
            'field_label'  => 'Show for login users',
            'field_type'   => 'checkbox',
            'page'         => 'jmb_captcha_options',
            'section'      => 'jmb_captcha_miscellaneous_section',
            'description'  => 'This will show and hide the captcha from comments and checkout forms.'
        ]);


    }

    public static function jmb_register_field($args) {
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
                    $args['sanitize_callback'] = function ($value) {
                        return $value === '1' ? '1' : '0';
                    };
                    break;

                case 'select':
                case 'text':
                default:
                    $args['sanitize_callback'] = 'sanitize_text_field';
                    break;
            }
        }

        // Register the setting with sanitization
        register_setting(
            $args['option_group'],
            $args['option_name'],
            [
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
