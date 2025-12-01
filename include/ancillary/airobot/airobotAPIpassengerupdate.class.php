<?php
require_once('airobotAPI.class.php');

class airobotAPIpassengerupdate extends airobotAPI
{
    protected $url_request = 'v1/ancillaries/{request_id}/passengers';

    private $travelers;

    public function __construct($request_id,$travelers)
    {
        $this->travelers   = $travelers;
        $this->param       = 'key';
        $this->token       = Config::get('suppliers')[api::$query->query['provider']]['ancillary_token'];
        $this->url_request = str_replace('{request_id}',$request_id,$this->url_request);
        $this->set_xml();
    }

    private function set_xml()
    {
        $this->set_options();
        $this->set_traveler();
        $this->xml = json_encode($this->request);
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
            'passengers' => [],
        ];
    }
}

class airobotAPIpassengerupdateResult
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
