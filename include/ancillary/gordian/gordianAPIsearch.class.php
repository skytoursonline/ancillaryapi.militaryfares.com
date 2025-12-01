<?php
class gordianAPIsearch extends gordianAPI
{
    protected $url_request = 'trip/{tripID}/search';

    public function __construct($trip_id)
    {
        $this->auth_header = ['Authorization: Basic '.base64_encode(Config::get('suppliers')[api::$query->query['provider']]['apikey'])];
        $this->url_request = str_replace(['{tripID}'],[$trip_id],$this->url_request);
        $this->set_xml();
    }

    private function set_xml()
    {
        $this->set_options();
        $this->xml = json_encode($this->request);
    }

    private function set_options()
    {
        $this->request = [
            'seat' => [
                'search' => true,
            ],
            'bag' => [
                'search' => false,
            ],
        ];
    }
}

class gordianAPIsearchResult
{
    public static $Result = [];

    public static function parse($response)
    {
        self::$Result = [];

        $result = json_decode($response,true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if ((isset($result['status']) && $result['status'] === 'failed') || isset($result['errors'])) {
                self::$Result['Fault']['faultcode']   = -1;
                self::$Result['Fault']['faultstring'] = $result['details'] ?? $result['errors'][0]['description'];
                return;
            }
            self::$Result = $result;
        } else {
            self::$Result['Fault']['faultcode']   = json_last_error();
            self::$Result['Fault']['faultstring'] = json_last_error_msg();
        }
    }
}
