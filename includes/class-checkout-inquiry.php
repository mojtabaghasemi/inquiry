<?php
if (!defined('ABSPATH')) exit;

class Inquiry_Checkout_Inquiry {
    public function __construct() {
        // Add custom fields to checkout form
        add_action('woocommerce_after_order_notes', array($this, 'add_custom_checkout_fields'));
    }

    // Add custom fields for National Code and Chassis Number
    public function add_custom_checkout_fields($checkout) {
        echo '<div id="inquiry_custom_fields"><h3>' . __('اطلاعات استعلام') . '</h3>';

        woocommerce_form_field('billing_national_code', array(
            'type'        => 'text',
            'class'       => array('billing_national_code form-row-wide'),
            'label'       => __('کد ملی'),
            'required'    => true,
        ), $checkout->get_value('billing_national_code'));

        woocommerce_form_field('billing_chassis_number', array(
            'type'        => 'text',
            'class'       => array('billing_chassis_number form-row-wide'),
            'label'       => __('شماره شاسی'),
            'required'    => true,
        ), $checkout->get_value('billing_chassis_number'));

        echo '</div>';
    }
}

new Inquiry_Checkout_Inquiry();
