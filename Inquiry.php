<?php
/**
 * Plugin Name: Inquiry
 * Description: افزونه استعلام از وزارت صمت در صفحه پرداخت ووکامرس
 * Version: 1.0.0
 * Author: Mojtaba Ghasemi
 * Text Domain: Daneshjooyar.com
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('INQUIRY_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('INQUIRY_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once INQUIRY_PLUGIN_PATH . 'includes/class-inquiry-admin.php';
require_once INQUIRY_PLUGIN_PATH . 'includes/class-inquiry-settings.php';
require_once INQUIRY_PLUGIN_PATH . 'includes/class-inquiry-api.php';
require_once INQUIRY_PLUGIN_PATH . 'includes/class-inquiry-frontend.php';

function inquiry_activate() {
    Inquiry_Settings::init();
}
register_activation_hook(__FILE__, 'inquiry_activate');

//---------------

add_action('woocommerce_checkout_process', 'validate_retail_webservice');

function validate_retail_webservice() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'inquiry_logs';

    // دریافت تنظیمات از WooCommerce
    $api_url = get_option('retail_api_url', '');
    $header_username = get_option('inquiry_retail_header_username', '');
    $header_password = get_option('inquiry_retail_header_password', '');
    $body_username = get_option('inquiry_retail_body_username', '');
    $body_password = get_option('inquiry_retail_body_password', '');
    $otp_code_password = get_option('inquiry_otpCode_password', '');

    // بررسی مقادیر ضروری
    if (empty($api_url) || empty($header_username) || empty($header_password) || empty($body_username) || empty($body_password) || empty($otp_code_password)) {
        wc_add_notice('Missing required settings for retail web service.', 'error');
        return;
    }

    // اطلاعات مشتری
    $mobile       = isset($_POST['tire_mobile']) ? sanitize_text_field($_POST['tire_mobile']) : '';
    $nationalCode = isset($_POST['tire_nid']) ? sanitize_text_field($_POST['tire_nid']) : '';
    $chassisNumber= isset($_POST['tire_chassis']) ? sanitize_text_field($_POST['tire_chassis']) : '';
    $noteNumber   = isset($_POST['tire_car_cart_number']) ? sanitize_text_field($_POST['tire_car_cart_number']) : '';
    $lastName   = isset($_POST['tire_last_name']) ? sanitize_text_field($_POST['tire_last_name']) : '';

    // remove 0 or +98 form mobile
    $mobile = normalize_mobile_number($mobile);

    if (empty($mobile)) {
        wc_add_notice('لطفاً شماره موبایل را وارد کنید.', 'error');
    }

    if (empty($nationalCode) || !preg_match('/^\d{10}$/', $nationalCode)) {
        wc_add_notice('لطفاً کد ملی معتبر وارد کنید.', 'error');
    }

    if (empty($chassisNumber)) {
        wc_add_notice('لطفاً شماره شاسی را وارد کنید.', 'error');
    }

    if (empty($noteNumber)) {
        wc_add_notice('لطفاً شماره برگه سبز را وارد کنید.', 'error');
    }

    if (empty($lastName)) {
        wc_add_notice('لطفاً نام و نام خانوادگی خود وارد کنید.', 'error');
    }

    $product_data = [];
    $person_national_id = '';
    $postal_code = '';
    $user_role_id = '';

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = $cart_item['product_id'];

        // get product meta
        $sold_tire_id = get_post_meta($product_id, '_sold_tire_id', true);
        $tire_role_code = get_post_meta($product_id, '_tire_role_code', true);
        $tire_seller_national_code = get_post_meta($product_id, '_tire_seller_national_code', true);
        $tire_postal_code = get_post_meta($product_id, '_tire_postal_code', true);

        if (empty($person_national_id) && !empty($tire_seller_national_code)) {
            $person_national_id = $tire_seller_national_code;
        }
        if (empty($postal_code) && !empty($tire_postal_code)) {
            $postal_code = $tire_postal_code;
        }
        if (empty($user_role_id) && !empty($tire_role_code)) {
            $user_role_id = $tire_role_code;
        }

        $product_data[] = [
            'Code'  => $sold_tire_id,
            'Count' => $cart_item['quantity'],
            'Price' => $cart_item['line_total'],
        ];
    }

    try {
        // create SoapClient
        $soapClient = new SoapClient(
            $api_url,
            [
                'login'    => $header_username,
                'password' => $header_password,
            ]
        );

        // set header SOAP
        $header = new SoapHeader(
            'http://pub-cix.ntsw.ir/',
            'Authentication',
            [
                'username' => $header_username,
                'password' => $header_password,
            ]
        );
        $soapClient->__setSoapHeaders($header);

        // تنظیم پارامترها
        $params = [
            'username'         => $body_username,
            'srvPass'          => $body_password,
            'password_otpCode' => $otp_code_password,
            'retail' => [
                'BuyerDatiles' => [
                    'BuyerMobile'     => $mobile,
                    'BuyerName'       => $lastName,
                    'BuyerNationalID' => $nationalCode,
                ],
                'Description'       => 'ثبت از API',
                'DocNumber'         => uniqid(),
                'DocumentDate'      => date('Y-m-d'),
                'PersonNationalID'  => $person_national_id,
                'PostalCode'        => $postal_code,
                'Stuffs_In'         => [
                    'RetailStuff' => $product_data,
                ],
                'UserRoleIDstr'     => $user_role_id,
                'statusAppointment' => 0,
                'tireCustomerInfo'  => [
                    'ChassisNo'    => $chassisNumber,
                    'NoteNumber'   => $noteNumber,
                ],
            ],
        ];

        // run response
        $response = $soapClient->__soapCall('SubmitRetail', [$params]);

        // check result
        if ($response && isset($response->SubmitRetailResult)) {
            if ($response->SubmitRetailResult->ResultCode !== 0) {
                $error_message = $response->SubmitRetailResult->ResultMessage ?? 'خطایی در ارسال به وب‌سرویس رخ داده است.';
                wc_add_notice($error_message, 'error');
                return;
            }

            $factor_id = $response->SubmitRetailResult->ObjList->RetailFactorVM->FactorID ?? null;

            // get last log
            $latest_log = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE mobile = %s AND chassis_no = %s ORDER BY created_at DESC LIMIT 1",
                $mobile,
                $chassisNumber
            ));

            if ($latest_log && $factor_id) {
                $wpdb->update(
                    $table_name,
                    ['factor_id' => $factor_id],
                    ['id' => $latest_log->id],
                    ['%s'],
                    ['%d']
                );
            }
        } else {
            wc_add_notice('پاسخ نامعتبر از وب‌سرویس دریافت شد.', 'error');
        }
    } catch (SoapFault $e) {
        wc_add_notice('خطا در اتصال به سامانه خرده‌فروشی: ' . $e->getMessage(), 'error');
    }
}


// ------------------

function normalize_mobile_number($mobile) {
    $normalized_mobile = preg_replace('/^(\+98|0)/', '', $mobile);
    return $normalized_mobile;
}

// Create log table
register_activation_hook(__FILE__, 'inquiry_create_log_table');

function inquiry_create_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'inquiry_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        national_code VARCHAR(50),
        mobile VARCHAR(50),
        chassis_no VARCHAR(100),
        note_number VARCHAR(100),
        allocation VARCHAR(50),
        fleet_type VARCHAR(50),
        result_message TEXT,
        response_data TEXT,
        status VARCHAR(20),
        factor_id VARCHAR(50) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}