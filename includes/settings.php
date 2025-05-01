<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class JMB_WC_Captcha_Settings {
    public static function init() {
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_menu', array(__CLASS__, 'add_settings_page'));
    }

    public static function add_settings_page() {
        add_options_page(
            'JMB CAPTCHA Settings',
            'JMB CAPTCHA',
            'manage_options',
            'jmb-captcha-settings',
            array(__CLASS__, 'render_settings_page')
        );
    }

    public static function render_settings_page() {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'googlerecaptcha';
        ?>
        <div class="wrap">
            <h1>JMB CAPTCHA Settings</h1>

            <h2 class="nav-tab-wrapper">
                <a href="?page=jmb-captcha-settings&tab=googlerecaptcha" class="nav-tab <?php echo $active_tab == 'googlerecaptcha' ? 'nav-tab-active' : ''; ?>">Google reCaptcha</a>
                <a href="?page=jmb-captcha-settings&tab=options" class="nav-tab <?php echo $active_tab == 'options' ? 'nav-tab-active' : ''; ?>">Options</a>
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
        add_settings_section('jmb_captcha_googlekeys_v3_section', '<h3>Google Captcha V3</h3><hr>', null, 'jmb_captcha_googlekeys');
        add_settings_section('jmb_captcha_woo_options_section', '<h3>Woocommerce Options</h3><hr>', null, 'jmb_captcha_options');
        add_settings_section('jmb_captcha_wp_options_section', '<h3>WordPress Options</h3><hr>', null, 'jmb_captcha_options');



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

        self::jmb_register_field([
            'option_group' => 'jmb_captcha_googlekeys',
            'option_name'  => 'jmb_captcha_v3_status',
            'field_label'  => 'Enable V3',
            'field_type'   => 'checkbox',
            'page'         => 'jmb_captcha_googlekeys',
            'section'      => 'jmb_captcha_googlekeys_v3_section',
        ]);

        self::jmb_register_field([
            'option_group' => 'jmb_captcha_googlekeys',
            'option_name'  => 'jmb_captcha_site_v3_key',
            'field_label'  => 'Site Key',
            'page'         => 'jmb_captcha_googlekeys',
            'section'      => 'jmb_captcha_googlekeys_v3_section',
        ]);

        self::jmb_register_field([
            'option_group' => 'jmb_captcha_googlekeys',
            'option_name'  => 'jmb_captcha_secret_v3_key',
            'field_label'  => 'Secret Key',
            'page'         => 'jmb_captcha_googlekeys',
            'section'      => 'jmb_captcha_googlekeys_v3_section',
        ]);

        if (class_exists('WooCommerce')) {

            self::jmb_register_field([
                'option_group' => 'jmb_captcha_options',
                'option_name'  => 'jmb_captcha_woo_register',
                'field_label'  => 'Woocommerce Registration Form',
                'field_type'   => 'checkbox',
                'page'         => 'jmb_captcha_options',
                'section'      => 'jmb_captcha_woo_options_section',
            ]);

            self::jmb_register_field([
                'option_group' => 'jmb_captcha_options',
                'option_name'  => 'jmb_captcha_woo_login',
                'field_label'  => 'Woocommerce Login Form',
                'field_type'   => 'checkbox',
                'page'         => 'jmb_captcha_options',
                'section'      => 'jmb_captcha_woo_options_section',
            ]);

            self::jmb_register_field([
                'option_group' => 'jmb_captcha_options',
                'option_name'  => 'jmb_captcha_woo_checkout',
                'field_label'  => 'Woocommerce Checkout',
                'field_type'   => 'checkbox',
                'page'         => 'jmb_captcha_options',
                'section'      => 'jmb_captcha_woo_options_section',
            ]);
            
            self::jmb_register_field([
                'option_group' => 'jmb_captcha_options',
                'option_name'  => 'jmb_captcha_woo_forgetpass',
                'field_label'  => 'Woocommerce Reset Password',
                'field_type'   => 'checkbox',
                'page'         => 'jmb_captcha_options',
                'section'      => 'jmb_captcha_woo_options_section',
            ]);

            self::jmb_register_field([
                'option_group' => 'jmb_captcha_options',
                'option_name'  => 'jmb_captcha_woo_comments',
                'field_label'  => 'Product Comment Form',
                'field_type'   => 'checkbox',
                'page'         => 'jmb_captcha_options',
                'section'      => 'jmb_captcha_woo_options_section',
            ]);

        }

        self::jmb_register_field([
            'option_group' => 'jmb_captcha_options',
            'option_name'  => 'jmb_captcha_wp_login',
            'field_label'  => 'WordPress Login',
            'field_type'   => 'checkbox',
            'page'         => 'jmb_captcha_options',
            'section'      => 'jmb_captcha_wp_options_section',
        ]);

        self::jmb_register_field([
            'option_group' => 'jmb_captcha_options',
            'option_name'  => 'jmb_captcha_wp_register',
            'field_label'  => 'WordPress Register',
            'field_type'   => 'checkbox',
            'page'         => 'jmb_captcha_options',
            'section'      => 'jmb_captcha_wp_options_section',
        ]);

        self::jmb_register_field([
            'option_group' => 'jmb_captcha_options',
            'option_name'  => 'jmb_captcha_wp_forget_pass',
            'field_label'  => 'WordPress Reset Password',
            'field_type'   => 'checkbox',
            'page'         => 'jmb_captcha_options',
            'section'      => 'jmb_captcha_wp_options_section',
        ]);

        self::jmb_register_field([
            'option_group' => 'jmb_captcha_options',
            'option_name'  => 'jmb_captcha_wp_comments',
            'field_label'  => 'WordPress Comments',
            'field_type'   => 'checkbox',
            'page'         => 'jmb_captcha_options',
            'section'      => 'jmb_captcha_wp_options_section',
        ]);

    }

    public static function jmb_register_field($args) {
        $defaults = [
            'option_group' => '',
            'option_name'  => '',
            'field_label'  => '',
            'field_type'   => 'text', // text | checkbox | select
            'callback'     => null,
            'page'         => '',
            'section'      => '',
            'choices'      => [], // for select dropdown
        ];
    
        $args = wp_parse_args($args, $defaults);
    
        // Register the setting
        register_setting($args['option_group'], $args['option_name']);
    
        // Auto create a basic callback if none provided
        if (!$args['callback']) {
            $args['callback'] = function() use ($args) {
                $value = get_option($args['option_name'], '');
    
                switch ($args['field_type']) {
                    case 'checkbox':
                        ?>
                        <input type="checkbox" name="<?php echo esc_attr($args['option_name']); ?>" value="1" <?php checked(1, $value, true); ?> />
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
