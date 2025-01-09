<?php
add_action('admin_menu', 'inquiry_add_admin_menu');
function inquiry_add_admin_menu() {
    add_menu_page('', 'لاگ‌ استعلام ها', 'manage_options', 'inquiry_results', 'display_inquiry_logs');
}

function display_inquiry_logs() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'inquiry_logs';
    $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

    echo '<h1>لاگ استعلام‌ها</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>کد ملی</th><th>موبایل</th><th>شاسی</th><th>نتیجه</th><th>وضعیت</th><th>تاریخ</th><th>شماره فاکتور</th><th>عملیات</th></tr></thead><tbody>';

    foreach ($logs as $log) {
        echo "<tr>
        <td>{$log->national_code}</td>
        <td>{$log->mobile}</td>
        <td>{$log->chassis_no}</td>
        <td>{$log->result_message}</td>
        <td>{$log->status}</td>
        <td>{$log->created_at}</td>
        <td>{$log->factor_id}</td>
        <td>
            <button class='view-log' data-log-id='{$log->id}'>مشاهده نتیجه</button>
            <div id='log-result-{$log->id}' style='display:none;'>
                <pre>" . esc_html($log->response_data) . "</pre>
            </div>
        </td>
    </tr>";
    }


    echo '</tbody></table>';

    echo '<div id="logModal" class="modal">
                <span class="close-modal">&times;</span>
                <h2>پاسخ وبسرویس</h2>
                <pre id="modal-body"></pre>
            </div>
            <div id="modalOverlay" class="modal-overlay"></div>';
}
