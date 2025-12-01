<?php
require_once('airobotAPI.class.php');

class airobotAPIseatmap extends airobotAPI
{
    protected $url_request = 'v1/ancillaries/{request_id}/journey/{journey_id}/seatmap';

    private $seatmap;

    public function __construct($request_id,$journey_id,$seatmap = null)
    {
        $this->seatmap     = $seatmap;
        $this->method      = 'get';
        $this->param       = 'key';
        $this->token       = Config::get('suppliers')[api::$query->query['provider']]['ancillary_token'];
        $this->url_request = str_replace(['{request_id}','{journey_id}'],[$request_id,$journey_id],$this->url_request);
    }

    public function set_xml()
    {
        $this->method = 'put';
        $this->set_options();
        $this->set_seatmap();
        $this->xml = json_encode($this->request);
    }

    private function set_seatmap()
    {
        foreach ($this->seatmap as $val) {
            $this->request['seatmap'][] = [
                'designator' => $val['designator'],
                'price'      => $val['price'],
            ];
        }
    }

    private function set_options()
    {
        $this->request = [
            'currency'     => 'EUR',
//            'exchangeRate' => 1.10,
            'seatmap'      => [],
        ];
    }
}

class airobotAPIseatmapResult
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
