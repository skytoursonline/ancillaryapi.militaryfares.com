<?php
require_once('airobotAPI.class.php');

class airobotAPIancillarycreate extends airobotAPI
{
    protected $url_request = 'v1/ancillaries/request';

    private $airline       = [];
    private $flight_number = [];
    private $travelers     = [];

    public function __construct($request)
    {
        $this->origin        = $request['origin'];
        $this->destination   = $request['destination'];
        $this->outdate       = $request['outdate'];
        $this->airline       = $request['airline'];
        $this->flight_number = $request['flight_number'];
        $this->travelers     = $request['travelers'];
        $this->param         = 'key';
        $this->token         = Config::get('suppliers')[api::$query->query['provider']]['ancillary_token'];
        $this->set_xml();
    }

    private function set_xml()
    {
        $this->set_data();
        $this->set_options();
        $this->set_destination();
        $this->set_traveler();
        $this->xml = json_encode($this->request);
    }

    private function set_data()
    {
        foreach ($this->outdate as &$value) {
            foreach ($value as &$val) {
                $val = (new DateTime($val))->format('Y-m-d H:i:s');
            }
        }
    }

    private function set_destination()
    {
        foreach ($this->origin as $fkey => $value) {
            foreach ($value as $skey => $val) {
                $this->request['journeys'][] = [
                    'airline'           => $this->airline[$fkey][$skey],
                    'departure_date'    => $this->outdate[$fkey][$skey],
                    'departure_airport' => $this->origin[$fkey][$skey],
                    'arrival_airport'   => $this->destination[$fkey][$skey],
                    'flight_number'     => $this->flight_number[$fkey][$skey],
                ];
            }
        }
    }

    private function set_traveler()
    {
        foreach ($this->travelers as $traveler) {
            $this->request['passengers'][] = [
                'name'      => $traveler['name'],
                'last_name' => $traveler['last_name'],
//                'dob'       => $traveler['dob'],
//                'type'      => $traveler['type'],
            ];
        }
    }

    private function set_options()
    {
        $this->request = [
            'type'       => 'seat',
            'journeys'   => [],
            'passengers' => [],
            'currency'   => 'EUR',
            'locale'     => 'it',
            'pricing'    => [
                'free_markup' => 2,
                'range' => [
                    [
                        'fixed' => 5,
                        'min'   => 0,
                        'max'   => 11,
                    ],
                    [
                        'percent' => 30,
                        'min'     => 11,
                        'max'     => 20,
                    ],
                    [
                        'percent' => 50,
                        'min'     => 20,
                        'max'     => 100,
                    ],
                ],
            ],
        ];
    }
}

class airobotAPIancillarycreateResult
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
