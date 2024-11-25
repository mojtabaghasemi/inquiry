<?php

class Inquiry_Frontend {
    public static function init() {
        add_action('woocommerce_review_order_before_payment', [__CLASS__, 'add_inquiry_button']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend_scripts']);

        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_scripts']);
    }

    public static function add_inquiry_button() {
        echo '<button type="button" id="inquiry-check-button" class="button alt">' . __('استعلام', 'inquiry') . '</button>';
        echo '<div id="inquiry-result"></div>';
        echo '<style>#place_order { opacity: 0.5; pointer-events: none; }</style>';
    }

    public static function enqueue_frontend_scripts() {
        wp_enqueue_style('inquiry-frontend-css', INQUIRY_PLUGIN_URL . 'assets/css/style.css', [], '1.0.0', 'all');
        wp_enqueue_script('inquiry-frontend-js', INQUIRY_PLUGIN_URL . 'assets/js/inquiry.js', ['jquery'], '1.0.0', true);

        wp_localize_script('inquiry-frontend-js', 'inquiry_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }

    public static function enqueue_admin_scripts($hook) {
        wp_enqueue_style('inquiry-admin-css', INQUIRY_PLUGIN_URL . 'assets/css/admin-style.css', [], '1.0.0', 'all');
        wp_enqueue_script('inquiry-admin-js', INQUIRY_PLUGIN_URL . 'assets/js/admin-script.js', ['jquery'], '1.0.0', true);
    }
}

Inquiry_Frontend::init();

