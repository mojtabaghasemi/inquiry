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
                'title'    => __('آدرس وبسرویس خرده فروشی', 'inquiry'),
                'id'       => 'retail_api_url',
                'type'     => 'text',
                'desc'     => __('', 'inquiry'),
                'default'  => '',
            ],
            [
                'title'    => __('Header Username', 'inquiry'),
                'id'       => 'inquiry_retail_header_username',
                'type'     => 'text',
                'desc'     => __('', 'inquiry'),
                'default'  => '',
            ],
            [
                'title'    => __('Header Password', 'inquiry'),
                'id'       => 'inquiry_retail_header_password',
                'type'     => 'password',
                'desc'     => __('', 'inquiry'),
                'default'  => '',
            ],
            [
                'title'    => __('Body Username', 'inquiry'),
                'id'       => 'inquiry_retail_body_username',
                'type'     => 'text',
                'desc'     => __('', 'inquiry'),
                'default'  => '',
            ],
            [
                'title'    => __('Body Password', 'inquiry'),
                'id'       => 'inquiry_retail_body_password',
                'type'     => 'password',
                'desc'     => __('', 'inquiry'),
                'default'  => '',
            ],
            [
                'title'    => __('ntsw.ir Password (password_otpCode)', 'inquiry'),
                'id'       => 'inquiry_otpCode_password',
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

    public function save() {
        // ذخیره تمام ورودی‌ها با استفاده از addslashes
        $fields = [
            'inquiry_header_username',
            'inquiry_header_password',
            'inquiry_body_username',
            'inquiry_body_password',
            'retail_api_url',
            'inquiry_retail_header_username',
            'inquiry_retail_header_password',
            'inquiry_retail_body_username',
            'inquiry_retail_body_password',
            'inquiry_otpCode_password'
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $value = addslashes($_POST[$field]);
                update_option($field, $value);
            }
        }
    }
}

return new Inquiry_WooCommerce_Settings();
