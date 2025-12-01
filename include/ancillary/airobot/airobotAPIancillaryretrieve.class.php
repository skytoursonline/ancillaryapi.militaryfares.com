<?php
require_once('airobotAPI.class.php');

class airobotAPIancillaryretrieve extends airobotAPI
{
    protected $url_request = 'v1/ancillaries/airline/{airline_iata}/items';

    public function __construct($airline_iata)
    {
        $this->method      = 'get';
        $this->param       = 'key';
        $this->token       = Config::get('suppliers')[api::$query->query['provider']]['ancillary_token'];
        $this->url_request = str_replace('{airline_iata}',$this->airline_iata,$this->url_request);
    }
}

class airobotAPIancillaryretrieveResult
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
