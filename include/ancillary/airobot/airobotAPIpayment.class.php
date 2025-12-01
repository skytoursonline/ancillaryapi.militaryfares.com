<?php
require_once('airobotAPI.class.php');

class airobotAPIpayment extends airobotAPI
{
    protected $url_request = 'v1/ancillaries/{request_id}/payment';

    private $journeys;
    private $travelers;

    public function __construct($request_id,$journeys,$travelers)
    {
        $this->journeys  = $journeys;
        $this->travelers   = $travelers;
        $this->param       = 'key';
        $this->token       = Config::get('suppliers')[api::$query->query['provider']]['ancillary_token'];
        $this->url_request = str_replace('{request_id}',$request_id,$this->url_request);
        $this->set_xml();
    }

    private function set_xml()
    {
        $this->set_data();
        $this->set_options();
        $this->set_journey();
        $this->set_traveler();
        $this->xml = json_encode($this->request);
    }

    private function set_data()
    {
        foreach ($this->outdate as &$val) {
            $val = (new DateTime($val))->format('Y-m-d H:i:s');
        }
        unset($val);
    }

    private function set_journey()
    {
        foreach ($this->journeys as $val) {
            $this->request['journeys'][] = [
                'id'        => $val['id'],
                'pnr'       => $val['pnr'],
                'email'     => $val['email'],
                'name'      => $val['name'],
                'last_name' => $val['last_name'],
                'seats'     => $val['seats'],
            ];
        }
    }

    private function set_traveler()
    {
        foreach ($this->travelers as $traveler) {
            $this->request['passengers'][] = [
                'id'        => $traveler['id'],
                'name'      => $traveler['name'],
                'last_name' => $traveler['last_name'],
//                'dob'         => $traveler['dob'],
//                'external_id' => $traveler['external_id'],
            ];
        }
    }

    private function set_options()
    {
        $this->request = [
            'type'         => 'seat',
            'journeys'     => [],
            'passengers'   => [],
            'payment_type' => 'cash',
        ];
    }
}

class airobotAPIpaymentResult
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
