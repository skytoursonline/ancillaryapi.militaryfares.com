<?php
class airobotAPI
{
    public $data;
    public $errno;
    public $xml;
    public $http_header = [
        'Content-Type: application/json',
    ];
    public $curl_options = [
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_POST           => true,
    ];

    protected $url_request;
    protected $method      = 'post';
    protected $request     = [];
    protected $origin      = [];
    protected $destination = [];
    protected $outdate     = [];
    protected $param;
    protected $token;

    public function request()
    {
        $this->curl_options += [
            CURLOPT_URL        => Config::get('suppliers')[api::$query->query['provider']]['url'].$this->url_request.'?'.$this->param.'='.$this->token,
            CURLOPT_HTTPHEADER => $this->http_header,
        ];
        if (!empty($this->xml)) {
            $this->curl_options += [
                CURLOPT_POSTFIELDS => $this->xml,
            ];
        }
        if ($this->method !== 'post') {
            $this->curl_options[CURLOPT_POST] = false;
        }
        if ($this->method === 'put') {
            $this->curl_options[CURLOPT_CUSTOMREQUEST] = 'PUT';
        }
        if ($this->method === 'delete') {
            $this->curl_options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        }
        $ch = curl_init();
        curl_setopt_array($ch,$this->curl_options);
        $this->data  = curl_exec($ch);
        $this->errno = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
    }
}
