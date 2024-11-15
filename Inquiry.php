<?php
/**
 * Plugin Name: Inquiry
 * Description: افزونه استعلام از وزارت صمت در صفحه پرداخت ووکامرس
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: inquiry
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// تعریف ثابت‌ها
define('INQUIRY_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('INQUIRY_PLUGIN_URL', plugin_dir_url(__FILE__));

// بارگذاری فایل‌های اصلی
require_once INQUIRY_PLUGIN_PATH . 'includes/class-inquiry-settings.php';
require_once INQUIRY_PLUGIN_PATH . 'includes/class-inquiry-api.php';
require_once INQUIRY_PLUGIN_PATH . 'includes/class-inquiry-frontend.php';

// فعال‌سازی تنظیمات افزونه
function inquiry_activate() {
    Inquiry_Settings::init();
}
register_activation_hook(__FILE__, 'inquiry_activate');
