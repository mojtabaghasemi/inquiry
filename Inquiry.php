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

add_action('woocommerce_order_status_processing', 'send_retail_data_to_webservice', 10, 1);

function send_retail_data_to_webservice($order_id) {
    $order = wc_get_order($order_id);

    // دریافت اطلاعات مشتری از فیلدهای سفارشی سفارش
    $buyer_last_name = get_post_meta($order_id, 'tire_last_name', true);
    $buyer_national_id = get_post_meta($order_id, 'tire_nid', true);
    $buyer_mobile = get_post_meta($order_id, 'tire_mobile', true);

    // اطلاعات مربوط به محصولات
    $product_data = [];
    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();
        $sold_tire_id = get_post_meta($product_id, '_sold_tire_id', true); // شناسه لاستیک
        $tire_role_code = get_post_meta($product_id, '_tire_role_code', true); // کد نقش فروشگاه
        $tire_postal_code = get_post_meta($product_id, '_tire_postal_code', true); // کد پستی انبار مبدا
        $tire_seller_national_code = get_post_meta($product_id, '_tire_seller_national_code', true); // کد / شناسه ملی فروشنده


        $product_data[] = [
            'Code'  => $sold_tire_id,
            'Count' => $item->get_quantity(),
        ];
    }


    $api_url = 'https://pub-cix.ntsw.ir/services/InternalTradeServices?wsdl';

    $options = [
        'location' => $api_url,
        'login'       => 'internalservice',  // نام کاربری هدر
        'password'    => 'ESBesb12?',   // رمز عبور هدر
        'trace'       => true,
        'exceptions'  => true,
        'cache_wsdl'  => WSDL_CACHE_NONE,
    ];

    try {
        $client = new SoapClient($api_url, $options);

        $params = [
            'username'         => '0065760621',
            'srvPass'          => 'Omid@1234',
            'password_otpCode' => '',
            'PersonNationalID' => '14008085937',
            'UserRoleIDstr'    => '2577711',
            'PostalCode'    => '1651669815',
            'UserRoleExtraFields' => [
                'PostalCode'    => '1651669815',
            ],
            'DocumentDate' => date('Y-m-d'),
            'DocNumber'        => '222',
            'Description'      => 'ثبت سند خرده فروشی',
            'BuyerDatiles'     => [
                'BuyerName'       => 'مجتبی قاسمی',
                'BuyerNationalID' => '5639879149',
                'BuyerMobile'     => '09928023782',
            ],
            'Stuffs_In'        => [
                'Code'  => '2800000115555',
                'Count' => '2',
            ],
            'statusAppointment'=> 0,
        ];

        $response = $client->__soapCall('SubmitRetail', [$params]);

        if ($response && isset($response->SubmitRetailResult)) {
            wp_die(print_r($response, true));exit();
            error_log("Retail data sent successfully: " . print_r($response, true));
        } else {
            wp_die(print_r($response, true));exit();
            error_log("SOAP response error: " . print_r($response, true));
        }
    } catch (SoapFault $e) {
        wp_die($e->getMessage());exit();
        error_log("Error sending order data to SOAP: " . $e->getMessage());
    }
}



// ------------------

add_action('woocommerce_product_options_inventory_product_data', function () {
    global $post;

    echo '<div class="options_group">';

    woocommerce_wp_text_input([
            'id' => '_tire_post_id',
            'label' => 'شناسه پست',
            'value' => get_post_meta($post->ID, '_tire_post_id', true)
        ]);

    woocommerce_wp_text_input([
            'id' => '_sold_tire_id',
            'label' => 'شناسه لاستیک',
            'value' => get_post_meta($post->ID, '_sold_tire_id', true)
        ]);

    woocommerce_wp_text_input([
            'id' => '_tire_role_code',
            'label' => 'کد نقش فروشگاه',
            'value' => get_post_meta($post->ID, '_tire_role_code', true)
        ]);

    woocommerce_wp_text_input([
            'id' => '_tire_seller_national_code',
            'label' => 'کد / شناسه ملی فروشنده',
            'value' => get_post_meta($post->ID, '_tire_seller_national_code', true)
        ]);

    woocommerce_wp_text_input([
            'id' => '_tire_postal_code',
            'label' => 'کد پستی انبار مبدا',
            'value' => get_post_meta($post->ID, '_tire_postal_code', true)
        ]);

    echo '</div>';
});

function save_product_id_field($post_id)
{
    update_post_meta($post_id, '_tire_post_id', sanitize_text_field($_POST['_tire_post_id'] ?? ''));
    update_post_meta($post_id, '_sold_tire_id', sanitize_text_field($_POST['_sold_tire_id'] ?? ''));
    update_post_meta($post_id, '_tire_role_code', sanitize_text_field($_POST['_tire_role_code'] ?? ''));
    update_post_meta($post_id, '_tire_postal_code', sanitize_text_field($_POST['_tire_postal_code'] ?? ''));
    update_post_meta($post_id, '_tire_seller_national_code', sanitize_text_field($_POST['_tire_seller_national_code'] ?? ''));
}

add_action('woocommerce_process_product_meta', 'save_product_id_field');




