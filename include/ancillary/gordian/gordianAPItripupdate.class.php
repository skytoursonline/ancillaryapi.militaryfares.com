<?php
class gordianAPItripupdate extends gordianAPI
{
    protected $url_request = 'trip/{tripID}';

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
    private $return_passenger_details = false;

    public function __construct($request,$trip_id)
    {
        $this->origin        = $request['origin'] ?? null;
        $this->destination   = $request['destination'] ?? null;
        $this->dep_date      = $request['dep_date'] ?? null;
        $this->arr_date      = $request['arr_date'] ?? null;
        $this->fare_basis    = $request['fare_basis'] ?? null;
        $this->fare_class    = $request['fare_class'] ?? null;
        $this->fare_family   = $request['fare_family'] ?? null;
        $this->airline       = $request['airline'] ?? null;
        $this->flight_number = $request['flight_number'] ?? null;
        $this->travelers     = $request['travelers'] ?? null;
        $this->language      = $request['language'] ?? null;
        $this->currency      = $request['currency'] ?? null;
        $this->country       = $request['country'] ?? null;
        $this->method        = 'patch';
        $this->auth_header   = ['Authorization: Basic '.base64_encode(Config::get('suppliers')[api::$query->query['provider']]['apikey'])];
        $this->url_request   = str_replace('{tripID}',$trip_id,$this->url_request);
        $this->set_xml();
    }

    private function set_xml()
    {
        $this->set_options();
        $this->set_destination();
        $this->set_traveler();
        $this->xml   = json_encode($this->request);
        $this->query = http_build_query([
            'return_passenger_details' => $this->return_passenger_details,
        ]);
    }

    private function set_destination()
    {
        if ($this->origin) {
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
        } else {
            unset($this->request['tickets']);
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
            'tickets'    => [],
            'passengers' => [],
        ];
    }
}

class gordianAPItripupdateResult
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
