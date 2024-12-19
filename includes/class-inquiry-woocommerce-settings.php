<?php

if (!defined('ABSPATH')) {
    exit;
}

class Inquiry_WooCommerce_Settings extends WC_Settings_Page {

    public function __construct() {
        $this->id    = 'inquiry';
        $this->label = __('وبسرویس استعلام', 'inquiry');
        parent::__construct();
    }

    public function get_settings() {
        return [
            [
                'title' => __('تنظیمات وبسرویس استعلام', 'inquiry'),
                'type'  => 'title',
                'desc'  => __('تنظیمات دسترسی به وبسرویس سامانه استعلام تایر.', 'inquiry'),
                'id'    => 'inquiry_settings',
            ],
            [
                'title'    => __('آدرس وبسرویس استعلام', 'inquiry'),
                'id'       => 'inquiry_api_url',
                'type'     => 'text',
                'desc'     => __('', 'inquiry'),
                'default'  => '',
            ],
            [
                'title'    => __('آدرس وبسرویس خرده فروشی', 'inquiry'),
                'id'       => 'retail_api_url',
                'type'     => 'text',
                'desc'     => __('', 'inquiry'),
                'default'  => '',
            ],
            [
                'title'    => __('Header Username', 'inquiry'),
                'id'       => 'inquiry_header_username',
                'type'     => 'text',
                'desc'     => __('', 'inquiry'),
                'default'  => '',
            ],
            [
                'title'    => __('Header Password', 'inquiry'),
                'id'       => 'inquiry_header_password',
                'type'     => 'password',
                'desc'     => __('', 'inquiry'),
                'default'  => '',
            ],
            [
                'title'    => __('Body Username', 'inquiry'),
                'id'       => 'inquiry_body_username',
                'type'     => 'text',
                'desc'     => __('', 'inquiry'),
                'default'  => '',
            ],
            [
                'title'    => __('Body Password', 'inquiry'),
                'id'       => 'inquiry_body_password',
                'type'     => 'password',
                'desc'     => __('', 'inquiry'),
                'default'  => '',
            ],
            [
                'type' => 'sectionend',
                'id'   => 'inquiry_settings',
            ],
        ];
    }
}

return new Inquiry_WooCommerce_Settings();
