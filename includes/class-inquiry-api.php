<?php

class Inquiry_API {
    protected $client;
    protected $params = [];

    public static function init() {
        add_action('wp_ajax_inquiry_check', [__CLASS__, 'handle_inquiry']);
        add_action('wp_ajax_nopriv_inquiry_check', [__CLASS__, 'handle_inquiry']);
    }

    public function __construct() {
        $api_url = get_option('inquiry_api_url');
        $options = $this->options();

        $this->client = new SoapClient($api_url, $options);
        $this->params['input'] = [
            'UserName' => get_option('inquiry_body_username'),
            'Password' => get_option('inquiry_body_password'),
        ];
    }

    protected function options(): array {
        return [
            'location' => get_option('inquiry_api_url'),
            'login'    => get_option('inquiry_header_username'),
            'password' => get_option('inquiry_header_password'),
            'trace'    => true,
            'exceptions' => true,
        ];
    }

    public function params($items): self {
        $this->params['input'] = array_merge($items, $this->params['input']);
        return $this;
    }

    public function get_allocation() {

        $national_code = sanitize_text_field($_POST['tire_nid']);
        $mobile_no = sanitize_text_field($_POST['tire_mobile']);
        $chassis_no = sanitize_text_field($_POST['tire_chassis']);
        $note_number = sanitize_text_field($_POST['tire_car_cart_number']);

        $tire_size = $this->get_tire_attribute('size');
        $tire_width = $this->get_tire_attribute('width');
        $tire_aspect_ratio = $this->get_tire_attribute('aspect-ratio');

        $params = [
            'CustomerInput' => [
                'NationalCode' => $national_code,
                'MobileNo'     => $mobile_no,
            ],
            'FleetInfoInput' => [
                'ChassisNo'           => $chassis_no,
                'NoteNumber'          => $note_number,
                'TireSizeTitle'       => $tire_size,
                'TireWidthTitle'      => $tire_width,
                'TireWallHeightTitle' => $tire_aspect_ratio,
            ],
        ];

        $this->params($params);

        try {
            $response = $this->client->__soapCall('NewGetTireAllocation', [$this->params]);
            return $response;
        } catch (SoapFault $e) {
            return $e->getMessage();
        }
    }

    protected function get_tire_attribute($attribute_slug) {
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = wc_get_product($cart_item['product_id']);
            $attributes = $product->get_attributes();

            if (isset($attributes['pa_' . $attribute_slug])) {
                $attribute = $attributes['pa_' . $attribute_slug];
                $options = $attribute->get_options();

                if (!empty($options)) {
                    $term_id = $options[0];
                    $term = get_term($term_id);
                    return $term ? $term->name : '';
                }
            }
        }
        return '';
    }

    public static function handle_inquiry() {
        $api = new self();
        $response = $api->get_allocation();

        if (is_string($response)) {
            wp_send_json_error(['message' => 'خطای SOAP: ' . $response]);
        } elseif (isset($response->NewGetTireAllocationResult) && $response->NewGetTireAllocationResult->ResultCode == 0) {
            $result = $response->NewGetTireAllocationResult;

            $message = 'استعلام موفق: ' . $result->ResultMessage;

            $allocation = $result->Obj->Allocation ?? null;
            $fleetType = $result->Obj->FleetType ?? null;

            wp_send_json_success([
                'message' => $message,
                'data' => [
                    'Allocation' => $allocation,
                    'FleetType'  => $fleetType,
                ]
            ]);
        } else {
            $message = $response->NewGetTireAllocationResult->ResultMessage ?? 'خطای نامشخص';
            wp_send_json_error(['message' => 'خطا در استعلام: ' . $message]);
        }

        wp_die();
    }


}

Inquiry_API::init();
