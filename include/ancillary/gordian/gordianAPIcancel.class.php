<?php
class gordianAPIcancel extends gordianAPI
{
    protected $url_request = 'trip/{tripID}/to_cancel';

    private $products = [];

    public function __construct($products,$trip_id)
    {
        $this->products    = $products;
        $this->auth_header = ['Authorization: Basic '.base64_encode(Config::get('suppliers')[api::$query->query['provider']]['apikey'])];
        $this->url_request = str_replace('{tripID}',$trip_id,$this->url_request);
        $this->set_xml();
    }

    private function set_xml()
    {
        $this->set_products();
        $this->xml = json_encode($this->request);
    }

    private function set_products()
    {
        $this->request = $this->products;
    }
}

class gordianAPIcancelResult
{
    public static $Result = [];

    public static function parse($response)
    {
        self::$Result = [];

        $result = json_decode($response,true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if ((isset($result['status']) && $result['status'] === 'failed') || isset($result['errors']) || isset($result['error_type'])) {
                self::$Result['Fault']['faultcode']   = -1;
                self::$Result['Fault']['faultstring'] = $result['details'] ?? $result['errors'][0]['description'] ?? $result['message'];
                return;
            }
            self::$Result = $result;
        } else {
            self::$Result['Fault']['faultcode']   = json_last_error();
            self::$Result['Fault']['faultstring'] = json_last_error_msg();
        }
    }
}
