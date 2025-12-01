<?php
class gordianAPI
{
    public $data;
    public $errno;
    public $xml;
    public $http_header = [
        'Content-Type: application/json',
    ];
    public $curl_options = [
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_ENCODING       => '',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_FOLLOWLOCATION => true,
    ];

    protected $url_request;
    protected $method = 'post';
    protected $query;
    protected $request;
    protected $auth_header;
    protected $lang = [
        'ar-EG',
        'ar-SA',
        'da-DK',
        'de-DE',
        'el-GR',
        'en-AU',
        'en-CA',
        'en-GB',
        'en-IL',
        'en-US',
        'es-ES',
        'et-EE',
        'fi-FI',
        'fr-FR',
        'id-ID',
        'it-IT',
        'ja-JP',
        'ko-KR',
        'lt-LT',
        'lv-LV',
        'nl-NL',
        'no-NB',
        'no-NO',
        'pl-PL',
        'pt-PT',
        'ro-RO',
        'sv-SE',
        'th-TH',
        'tr-TR',
        'vi-VN',
        'zh-CN',
        'zh-HK',
    ];

    public function request()
    {
        $this->curl_options += [
            CURLOPT_URL        => Config::get('suppliers')[api::$query->query['provider']]['url'].$this->url_request.((!empty($this->query)) ? '?'.$this->query : ''),
            CURLOPT_HTTPHEADER => array_merge($this->http_header,$this->auth_header),
        ];
        if ($this->method === 'post') {
            $this->curl_options += [
                CURLOPT_POST => true,
            ];
            if (!empty($this->xml)) {
                $this->curl_options += [
                    CURLOPT_POSTFIELDS => $this->xml,
                ];
            }
        }
        if ($this->method === 'patch') {
            $this->curl_options += [
                CURLOPT_CUSTOMREQUEST => 'PATCH',
                CURLOPT_POSTFIELDS    => $this->xml,
            ];
        }
        if ($this->method === 'put') {
            $this->curl_options += [
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS    => $this->xml,
            ];
        }
        $ch = curl_init();
        curl_setopt_array($ch,$this->curl_options);
        $this->data  = curl_exec($ch);
        $this->errno = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
    }
}
