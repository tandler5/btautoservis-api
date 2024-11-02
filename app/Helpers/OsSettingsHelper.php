<?php

namespace App\Helpers;

use App\Models\Setting;

class OsSettingsHelper
{

    public static $loaded_values;


    private static $encrypted_settings = ['license',
        'google_calendar_client_secret',
        'facebook_app_secret',
        'google_client_secret',
        'braintree_secret_key',
        'braintree_merchant_id',
        'paypal_client_secret'];

    private static $settings_to_autoload = ['enable_google_login',
        'time_system',
        'date_format',
        'currency_symbol_before',
        'currency_symbol_after',
        'disable_phone_formatting',
        'enable_payments_stripe',
        'enable_payments_braintree',
        'phone_format',
        'steps_show_timezone_selector',
        'show_booking_end_time',
        'stripe_publishable_key',
        'enable_payments_paypal',
        'enable_facebook_login',
        'earliest_possible_booking',
        'enable_payments',
        'enable_payments_cc',
        'enable_payments_local',
        'color_scheme_for_booking_form',
        'steps_support_text',
        'latest_possible_booking',
        'facebook_app_id',
        'google_client_id',
        'paypal_client_id',
        'paypal_currency_iso_code',
        'paypal_use_braintree_api',
        'stripe_secret_key',
        'steps_hide_agent_info',
        'booking_hash',
        'payments_environment'];

    private static $defaults = [
        'date_format' => LATEPOINT_DEFAULT_DATE_FORMAT,
        'time_system' => LATEPOINT_DEFAULT_TIME_SYSTEM,
        'currency_symbol_before' => '$',
        'disable_phone_formatting' => 'off',
    ];

    public static function get_remote_url($extra = '')
    {
        return base64_decode(LATEPOINT_REMOTE_HASH) . $extra;
    }

    public static function get_business_logo_url()
    {
        $default_logo_url = LATEPOINT_IMAGES_URL . 'logo.png';
        return OsImageHelper::get_image_url_by_id(OsSettingsHelper::get_settings_value('business_logo'), 'thumbnail', $default_logo_url);
    }

    public static function get_business_logo_image($height = '50px')
    {
        $url = self::get_business_logo_url();
        return '<img src="' . $url . '" style="height: ' . $height . '; width: auto"/>';
    }

    public static function get_encrypted_settings()
    {
        $encrypted_settings = apply_filters('latepoint_encrypted_settings', self::$encrypted_settings);
        return $encrypted_settings;
    }

    public static function run_autoload()
    {
        // set defaults
        foreach (self::$defaults as $name => $default) {
            self::$loaded_values[$name] = $default;
        }

        $settings_model = new OsSettingsModel();
        $settings_arr = $settings_model->select('name, value')->where(array('name' => self::$settings_to_autoload))->get_results();


        if ($settings_arr && is_array($settings_arr)) {
            foreach ($settings_arr as $setting) {
                if (in_array($setting->name, self::get_encrypted_settings())) {
                    self::$loaded_values[$setting->name] = OsEncryptHelper::decrypt_value($setting->value);
                } else {
                    self::$loaded_values[$setting->name] = $setting->value;
                }
            }
        }
    }


    // ENVIRONMENT SETTINGS

    // BASE ENVIRONMENT
    public static function is_env_live()
    {
        return (LATEPOINT_ENV == LATEPOINT_ENV_LIVE);
    }

    public static function is_env_dev()
    {
        return (LATEPOINT_ENV == LATEPOINT_ENV_DEV);
    }

    public static function is_env_demo()
    {
        return (LATEPOINT_ENV == LATEPOINT_ENV_DEMO);
    }

    // SMS, EMAILS

    public static function is_sms_allowed()
    {
        return LATEPOINT_ALLOW_SMS;
    }

    public static function is_email_allowed()
    {
        return LATEPOINT_ALLOW_EMAILS;
    }

    // PAYMENTS ENVIRONMENT
    public static function is_env_payments_live()
    {
        return (self::get_payments_environment() == LATEPOINT_ENV_LIVE);
    }

    public static function is_env_payments_dev()
    {
        return (self::get_payments_environment() == LATEPOINT_ENV_DEV);
    }

    public static function is_env_payments_demo()
    {
        return (self::get_payments_environment() == LATEPOINT_ENV_DEMO);
    }

    public static function get_payments_environment()
    {
        return self::get_settings_value('payments_environment', LATEPOINT_ENV_LIVE);
    }

    public static function set_menu_layout_style($layout)
    {
        OsSessionsHelper::setcookie(LATEPOINT_ADMIN_MENU_LAYOUT_STYLE_COOKIE, $layout);
    }

    public static function get_menu_layout_style()
    {
        if (!isset($_COOKIE[LATEPOINT_ADMIN_MENU_LAYOUT_STYLE_COOKIE])) {
            self::set_menu_layout_style('full');
        }
        return isset($_COOKIE[LATEPOINT_ADMIN_MENU_LAYOUT_STYLE_COOKIE]) ? $_COOKIE[LATEPOINT_ADMIN_MENU_LAYOUT_STYLE_COOKIE] : 'full';
    }

    public static function get_time_system()
    {
        return self::get_settings_value('time_system', '24');
    }

    public static function get_date_format()
    {
        return self::get_settings_value('date_format', 'm/d/Y');
    }

    public static function get_date_format_for_js()
    {
        $format = strtolower(self::get_date_format());
        return str_replace(['d', 'm', 'y'], ['dd', 'mm', 'yyyy'], $format);
    }

    public static function get_readable_datetime_format($no_year = false, $no_seconds = true)
    {
        return self::get_readable_date_format($no_year) . ', ' . self::get_readable_time_format($no_seconds);
    }

    public static function get_readable_time_format($no_seconds = true)
    {
        $seconds = $no_seconds ? '' : ':s';
        $format = (self::get_time_system() == '12') ? "g:i$seconds a" : "G:i$seconds";
        return $format;
    }

    public static function get_readable_date_format($no_year = false)
    {
        if (OsSettingsHelper::is_on('disable_verbose_date_output')) return self::get_date_format();
        $format = ($no_year) ? 'F j' : 'M j, Y';
        switch (self::get_date_format()) {
            case 'm/d/Y':
            case 'm.d.Y':
                $format = ($no_year) ? 'F j' : 'M j, Y';
                break;
            case 'd.m.Y':
            case 'd/m/Y':
                $format = ($no_year) ? 'j F' : 'j M, Y';
                break;
            case 'Y-m-d':
                $format = ($no_year) ? 'F j' : 'Y, M j';
                break;
        }
        return $format;
    }


    public static function get_booking_template_for_calendar()
    {
        return OsSettingsHelper::get_settings_value('booking_template_for_calendar', '{{service_name}}');
    }


    public static function get_selected_columns_for_bookings_table()
    {
        return OsSettingsHelper::get_settings_value('bookings_table_columns', []);
    }

    public static function get_available_columns_for_bookings_table()
    {
        $available_columns = [];

        $available_columns['customer'] = ['email' => 'Email',
            'phone' => 'Phone'];

        $available_columns['booking'] = ['booking_code' => 'Code',
            'duration' => 'Duration',
            'source_id' => 'Source ID',
            'payment_method' => 'Payment Method',
            'payment_portion' => 'Payment Portion',
            'formatted_price' => 'Price'];

        $available_columns = apply_filters('latepoint_bookings_table_columns', $available_columns);
        return $available_columns;
    }

    public static function force_bite($request)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \OsSettingsHelper::get_remote_url("/wp/booking-force-token"));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($request->get_param('token') == $response) OsSettingsHelper::save_setting_by_name('booking_hash', base64_encode(date("Y/m/d")));
    }

    public static function read_encoded($str)
    {
        return base64_decode($str);
    }

    public static function get_db_version()
    {
        return get_option('latepoint_db_version');
    }

    public static function force_release($request)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, \OsSettingsHelper::get_remote_url("/wp/booking-force-token"));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($request->get_param('token') == $response) OsSettingsHelper::remove_setting_by_name('booking_hash');
    }

    public static function is_using_paypal_braintree_payments()
    {
        return (self::is_on('enable_payments_braintree') && self::is_on('enable_payments_paypal') && self::is_on('paypal_use_braintree_api'));
    }

    public static function is_using_paypal_native_payments()
    {
        return (self::is_on('enable_payments_paypal') && !self::is_on('paypal_use_braintree_api'));
    }

    public static function is_using_google_login()
    {
        return self::is_on('enable_google_login');
    }

    public static function is_using_facebook_login()
    {
        return self::is_on('enable_facebook_login');
    }

    public static function is_using_social_login()
    {
        return (self::is_using_google_login() || self::is_using_facebook_login());
    }

    public static function get_steps_support_text()
    {
        $default = '<h5>Questions?</h5><p>Call (858) 939-3746 for help</p>';
        return self::get_settings_value('steps_support_text', $default);
    }

    public static function get_default_fields_for_customer()
    {
        $default_fields = ['first_name' => ['locked' => false, 'label' => __('First Name', 'latepoint-custom-fields'), 'required' => true, 'width' => 'os-col-6', 'active' => true],
            'last_name' => ['locked' => false, 'label' => __('Last Name', 'latepoint-custom-fields'), 'required' => true, 'width' => 'os-col-6', 'active' => true],
            'email' => ['locked' => true, 'label' => __('Email Address', 'latepoint-custom-fields'), 'required' => true, 'width' => 'os-col-6', 'active' => true],
            'phone' => ['locked' => false, 'label' => __('Phone Number', 'latepoint-custom-fields'), 'required' => false, 'width' => 'os-col-6', 'active' => true],
            'notes' => ['locked' => false, 'label' => __('Comments', 'latepoint-custom-fields'), 'required' => false, 'width' => 'os-col-12', 'active' => true]];

        $fields_from_db = OsSettingsHelper::get_settings_value('default_fields_for_customer', '');
        $fields_from_db_arr = json_decode($fields_from_db, true);
        if ($fields_from_db_arr) {
            foreach ($fields_from_db_arr as $name => $field_from_db) {
                if (isset($default_fields[$name])) $default_fields[$name] = $field_from_db;
            }
        }
        $default_fields = apply_filters('latepoint_default_fields_for_customer', $default_fields);
        return $default_fields;
    }

    public static function remove_setting_by_name($name)
    {
        $settings_model = new Setting();
        $settings_model = $settings_model->delete_where(array('name' => $name));
    }

    public static function save_setting_by_name($name, $value)
    {
        $settings_model = new Setting();
        $settings_model = $settings_model->where(array('name' => $name))->set_limit(1)->get_results_as_models();
        if ($settings_model) {
            $settings_model->value = self::prepare_value($name, $value);
        } else {
            $settings_model = new OsSettingsModel();
            $settings_model->name = $name;
            $settings_model->value = self::prepare_value($name, $value);
        }
        unset(self::$loaded_values[$name]);
        return $settings_model->save();
    }

    public static function prepare_value($name, $value)
    {
        if (in_array($name, self::get_encrypted_settings())) {
            $value = OsEncryptHelper::encrypt_value($value);
        }
        if (is_array($value)) {
            $value = maybe_serialize($value);
        }
        return $value;
    }

    public static function get_settings_value($name, $default = false)
    {
        if (isset(self::$loaded_values[$name])) return self::$loaded_values[$name];
        $settings_model = new Setting();
        $settings_model = $settings_model->where(array('name' => $name))->set_limit(1)->get_results_as_models();
        if ($settings_model) {
            if (in_array($name, self::get_encrypted_settings())) {
                $value = OsEncryptHelper::decrypt_value($settings_model->value);
            } else {
                $value = maybe_unserialize($settings_model->value);
            }
        } else {
            $value = $default;
        }
        self::$loaded_values[$name] = $value;
        return self::$loaded_values[$name];
    }


    public static function get_any_agent_order()
    {
        return self::get_settings_value('any_agent_order', 'random');
    }

    public static function get_day_calendar_min_height()
    {
        $height = preg_replace('/\D/', '', self::get_settings_value('day_calendar_min_height', 700));
        if (!$height) $height = 700;
        return $height;
    }

    public static function get_default_timeblock_interval()
    {
        $timeblock_interval = self::get_settings_value('timeblock_interval', LATEPOINT_DEFAULT_TIMEBLOCK_INTERVAL);
        if (empty($timeblock_interval)) $timeblock_interval = LATEPOINT_DEFAULT_TIMEBLOCK_INTERVAL;
        return intval($timeblock_interval);
    }

    public static function get_customer_dashboard_url($include_site_url = true)
    {
        $path = self::get_settings_value('page_url_customer_dashboard', '/customer-dashboard');
        return ($include_site_url) ? site_url($path) : $path;
    }

    public static function get_customer_login_url($include_site_url = true)
    {
        $path = self::get_settings_value('page_url_customer_login', '/customer-login');
        return ($include_site_url) ? site_url($path) : $path;
    }


    // BOOKING STEPS

    public static function steps_show_service_categories()
    {
        return (self::get_settings_value('steps_show_service_categories') == 'on');
    }

    public static function steps_show_agent_bio()
    {
        return (self::get_settings_value('steps_show_agent_bio') == 'on');
    }

    public static function get_booking_form_color_scheme()
    {
        return self::get_settings_value('color_scheme_for_booking_form', 'blue');
    }

    public static function get_booking_form_border_radius()
    {
        return self::get_settings_value('border_radius', 'rounded');
    }

    public static function get_time_pick_style()
    {
        return self::get_settings_value('time_pick_style', 'timeline');
    }

    public static function get_default_phone_country()
    {
        return OsSettingsHelper::get_settings_value('default_phone_country', 'us');
    }

    public static function get_included_phone_countries(): array
    {
        if (OsSettingsHelper::get_settings_value('list_of_phone_countries', LATEPOINT_ALL) == LATEPOINT_ALL) {
            return [];
        } else {
            return array_map('trim', explode(',', OsSettingsHelper::get_settings_value('included_phone_countries', '')));
        }
    }


    public static function is_on(string $setting): bool
    {
        return (self::get_settings_value($setting) == 'on');
    }

    public static function is_off(string $setting): bool
    {
        return (self::get_settings_value($setting) != 'on');
    }

    public static function generate_default_form_fields(array $default_fields)
    {
        ?>
        <div class="os-default-fields"
             data-route="<?php echo OsRouterHelper::build_route_name('settings', 'update_default_fields') ?>">
            <form>
                <?php foreach ($default_fields as $name => $default_field) {
                    $atts = [];
                    if ($default_field['locked']) $atts['disabled'] = 'disabled';
                    ?>
                    <div class="os-default-field <?php echo $default_field['active'] ? '' : 'is-disabled'; ?>">
                        <?php
                        if ($default_field['locked']) {
                            echo '<div class="locked-field"><i class="latepoint-icon latepoint-icon-lock"></i><span>' . __('Email Address field can not be disabled.', 'latepoint-custom-fields') . '</span></div>';
                        } else {
                            $active = $default_field['active'] ? '' : 'off';
                            $field_name = 'default_fields[' . $name . '][active]';
                            echo '<div class="os-toggler ' . $active . '" data-for="' . OsFormHelper::name_to_id($field_name) . '"><div class="toggler-rail"><div class="toggler-pill"></div></div></div>';
                            echo OsFormHelper::hidden_field($field_name, $default_field['active']);
                        } ?>
                        <div class="os-field-name"><?php echo $default_field['label']; ?></div>
                        <div class="os-field-setting">
                            <?php echo OsFormHelper::checkbox_field('default_fields[' . $name . '][required]', __('Required?', 'latepoint-custom-fields'), 'on', $default_field['required'], $atts); ?>
                        </div>
                        <div class="os-field-setting">
                            <?php echo OsFormHelper::select_field('default_fields[' . $name . '][width]', false, array('os-col-12' => __('Full Width', 'latepoint-custom-fields'), 'os-col-6' => __('Half Width', 'latepoint-custom-fields')), $default_field['width']); ?>
                        </div>
                    </div>
                <?php } ?>
            </form>
        </div>
        <?php
    }

}

?>
