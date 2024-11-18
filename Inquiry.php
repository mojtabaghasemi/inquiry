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
    error_log( 'Hello World!' );
    Inquiry_Settings::init();
}
register_activation_hook(__FILE__, 'inquiry_activate');

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
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}


add_action('admin_menu', 'inquiry_add_admin_menu');

function inquiry_add_admin_menu() {
    add_menu_page('نتایج', 'نتایج', 'manage_options', 'inquiry_results', 'display_inquiry_logs');
    add_submenu_page('inquiry_results', 'لاگ‌ها', 'لاگ‌ها', 'manage_options', 'inquiry_logs', 'display_inquiry_logs');
}

function display_inquiry_logs() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'inquiry_logs';
    $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

    echo '<h1>لاگ استعلام‌ها</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>کد ملی</th><th>موبایل</th><th>شاسی</th><th>نتیجه</th><th>وضعیت</th><th>تاریخ</th><th>عملیات</th></tr></thead><tbody>';

    foreach ($logs as $log) {
        echo "<tr>
            <td>{$log->national_code}</td>
            <td>{$log->mobile}</td>
            <td>{$log->chassis_no}</td>
            <td>{$log->result_message}</td>
            <td>{$log->status}</td>
            <td>{$log->created_at}</td>
            <td>
                <button class='view-log' data-log-id='{$log->id}'>مشاهده نتیجه</button>
                <div id='log-result-{$log->id}' class='log-result' style='display:none;'>
                    <pre>{$log->response_data}</pre>
                </div>
            </td>
        </tr>";
    }

    echo '</tbody></table>';
}



