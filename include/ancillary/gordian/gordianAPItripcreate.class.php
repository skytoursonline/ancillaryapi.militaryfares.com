<?php
class gordianAPItripcreate extends gordianAPI
{
    protected $url_request = 'trip';

    private $origin        = [];
    private $destination   = [];
    private $dep_date      = [];
    private $arr_date      = [];
    private $fare_basis    = [];
    private $fare_class    = [];
    private $fare_family   = [];
    private $airline       = [];
    private $flight_number = [];
    private $travelers     = [];
    private $language;
    private $currency;
    private $country;

    public function __construct($request)
    {
        $this->origin        = $request['origin'];
        $this->destination   = $request['destination'];
        $this->dep_date      = $request['dep_date'];
        $this->arr_date      = $request['arr_date'];
        $this->fare_basis    = $request['fare_basis'];
        $this->fare_class    = $request['fare_class'];
        $this->fare_family   = $request['fare_family'];
        $this->airline       = $request['airline'];
        $this->flight_number = $request['flight_number'];
        $this->travelers     = $request['travelers'];
        $this->language      = $request['language'];
        $this->currency      = $request['currency'];
        $this->country       = $request['country'];
        $this->auth_header   = ['Authorization: Basic '.base64_encode(Config::get('suppliers')[api::$query->query['provider']]['apikey'])];
        $this->set_xml();
    }

    private function set_xml()
    {
        $this->set_options();
        $this->set_destination();
        $this->set_traveler();
        $this->xml = json_encode($this->request);
    }

    private function set_destination()
    {
        foreach ($this->origin as $i => $value) {
            $this->request['tickets'][0]['status'] = 'offered';
            foreach ($value as $j => $val) {
                $this->request['tickets'][0]['journeys'][$i]['segments'][] = [
                    'departure_airport'       => $this->origin[$i][$j],
                    'departure_time'          => $this->dep_date[$i][$j],
                    'arrival_airport'         => $this->destination[$i][$j],
                    'arrival_time'            => $this->arr_date[$i][$j],
                    'marketing_airline'       => $this->airline[$i][$j],
                    'marketing_flight_number' => $this->flight_number[$i][$j],
                    'fare_basis'              => $this->fare_basis[$i][$j],
                    'fare_class'              => $this->fare_class[$i][$j],
                    'fare_family'             => $this->fare_family[$i][$j],
                ];
            }
        }
    }

    private function set_traveler()
    {
        foreach ($this->travelers as $traveler) {
            $this->request['passengers'][] = $traveler;
        }
    }

    private function set_options()
    {
        $this->request = [
            'search' => [
                'seat' => [
                    'search' => true,
                ],
                'bag' => [
                    'search' => true,
                ],
            ],
            'tickets'    => [],
            'language'   => 'en-US',
            'currency'   => $this->currency,
            'country'    => $this->country,
            'passengers' => [],
        ];
    }
}

class gordianAPItripcreateResult
{
    public static $Result = [];

    public static function parse($response)
    {
        self::$Result = [];

        $result = json_decode($response,true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if ((isset($result['status']) && $result['status'] === 'failed') || isset($result['errors']) || !empty($result['message'])) {
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
