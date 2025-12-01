<?php
class gordianAPIsubscribe extends gordianAPI
{
    protected $url_request = 'callback_subscription';

    private $target;
    private $event;

    public function __construct($url,$event)
    {
        $this->target      = $url;
        $this->event       = $event;
        $this->auth_header = ['Authorization: Basic '.base64_encode(Config::get('suppliers')[api::$query->query['provider']]['apikey'])];
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
            'delivery_method' => 'http',
            'target'          => $this->target,
            'event_name'      => $this->event,
        ];
    }
}

class gordianAPIsubscribeResult
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
