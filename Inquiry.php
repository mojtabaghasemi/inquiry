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



