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

//        $national_code = sanitize_text_field($_POST['tire_nid']);
//        $mobile_no = sanitize_text_field($_POST['tire_mobile']);
//        $chassis_no = sanitize_text_field($_POST['tire_chassis']);
//        $note_number = sanitize_text_field($_POST['tire_car_cart_number']);

        $national_code = '5639879149';
        $mobile_no = '9928023782';
        $chassis_no = '128872';
        $note_number = '11181683910594';

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

        $national_code = '5639879149';
        $mobile_no = '9928023782';
        $chassis_no = '128872';
        $note_number = '11181683910594';

        if (is_string($response)) {
            $message = 'خطای SOAP: ' . $response;
            $api->inquiry_log($national_code, $mobile_no, $chassis_no, $note_number, null, null, $message, json_encode($response), 'error');
            wp_send_json_error(['message' => $message]);
        } elseif (isset($response->NewGetTireAllocationResult) && $response->NewGetTireAllocationResult->ResultCode == 0) {
            $result = $response->NewGetTireAllocationResult;
            $message = 'استعلام موفق: ' . $result->ResultMessage;
            $allocation = $result->Obj->Allocation ?? null;
            $fleetType = $result->Obj->FleetType ?? null;

            $api->inquiry_log($national_code, $mobile_no, $chassis_no, $note_number, $allocation, $fleetType, $message, json_encode($response), 'success');

            wp_send_json_success([
                'message' => $message,
                'data' => [
                    'Allocation' => $allocation,
                    'FleetType'  => $fleetType,
                ]
            ]);
        } else {
            $message = $response->NewGetTireAllocationResult->ResultMessage ?? 'خطای نامشخص';
            $api->inquiry_log($national_code, $mobile_no, $chassis_no, $note_number, null, null, $message, json_encode($response), 'error');
            wp_send_json_error(['message' => 'خطا در استعلام: ' . $message]);
        }

        wp_die();
    }


    function inquiry_log($national_code, $mobile, $chassis_no, $note_number, $allocation, $fleet_type, $result_message, $response_data, $status) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'inquiry_logs';

        $wpdb->insert($table_name, [
            'national_code'   => $national_code,
            'mobile'          => $mobile,
            'chassis_no'      => $chassis_no,
            'note_number'     => $note_number,
            'allocation'      => $allocation,
            'fleet_type'      => $fleet_type,
            'result_message'  => $result_message,
            'response_data'   => $response_data,
            'status'          => $status,
            'created_at'      => current_time('mysql')
        ]);

        if ($wpdb->last_error) {
            wp_die("Database Error: " . $wpdb->last_error);
        }

    }

}

Inquiry_API::init();
