<?php
require_once('airobotAPI.class.php');

class airobotAPIancillaryremove extends airobotAPI
{
    protected $url_request = 'v1/ancillaries/airline/{airline_iata}/items';

    private $items;

    public function __construct($airline_iata,$items = null)
    {
        $this->items       = $items;
        $this->method      = 'delete';
        $this->param       = 'key';
        $this->token       = Config::get('suppliers')[api::$query->query['provider']]['ancillary_token'];
        $this->url_request = str_replace('{airline_iata}',$this->airline_iata,$this->url_request);
        $this->set_xml();
    }

    private function set_xml()
    {
        $this->set_options();
        $this->set_items();
        $this->xml = json_encode($this->request);
    }

    private function set_items()
    {
        foreach ($this->items as $val) {
            $this->request['items'][] = [
                'code'         => $val['code'],
                'passenger_id' => $val['passenger_id'],
                'journey_id'   => $val['journey_id'],
            ];
        }
    }

    private function set_options()
    {
        $this->request = [
            'items' => [],
        ];
    }
}

class airobotAPIancillaryremoveResult
{
    public static $Result = [];

    public static function parse($response)
    {
        self::$Result = [];

        $result = json_decode($response,true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($result['error'])) {
                self::$Result['Fault']['faultcode']   = (isset($result['error']['code'])) ? $result['error']['code'] : -1;
                self::$Result['Fault']['faultstring'] = (isset($result['error']['message'])) ? $result['error']['message'] : $result['message'];
                return;
            } elseif (isset($result['status']) && $result['status'] === 'fail') {
                self::$Result['Fault']['faultcode']   = $result['errorCode'];
                self::$Result['Fault']['faultstring'] = $result['message'];
                return;
            }
            self::$Result = $result;
        } else {
            self::$Result['Fault']['faultcode']   = json_last_error();
            self::$Result['Fault']['faultstring'] = json_last_error_msg();
        }
    }
}
