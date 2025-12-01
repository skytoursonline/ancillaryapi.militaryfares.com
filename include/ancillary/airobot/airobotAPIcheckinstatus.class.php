<?php
require_once('airobotAPI.class.php');

class airobotAPIcheckinstatus extends airobotAPI
{
    protected $url_request = 'v2/checkin/{request_id}';

    public function __construct($request)
    {
        $this->method      = 'get';
        $this->param       = 'api_token';
        $this->token       = Config::get('suppliers')[api::$query->query['provider']]['checkin_token'];
        $this->url_request = str_replace('{request_id}',$request['request_id'],$this->url_request);
//        $this->http_header[] = 'expectedTestResult: success'; // "success" or "failed" (used for testing)
    }
}

class airobotAPIcheckinstatusResult
{
    public static $Result = [];

    public static function parse($response)
    {
        self::$Result = [];

        $result = json_decode($response,true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($result['status']) && $result['status'] === 'fail') {
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
