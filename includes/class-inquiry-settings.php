<?php

class Inquiry_Settings {
    public static function init() {
        add_filter('woocommerce_get_settings_pages', [__CLASS__, 'add_settings_page']);
    }

    public static function add_settings_page($settings) {
        $settings[] = include INQUIRY_PLUGIN_PATH . 'includes/class-inquiry-woocommerce-settings.php';
        return $settings;
    }
}

Inquiry_Settings::init();
