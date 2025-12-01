<?php
require_once('airobotAPI.class.php');

class airobotAPIcheckincreate extends airobotAPI
{
    protected $url_request = 'v2/checkin';

    private $dep_date      = [];
    private $arr_date      = [];
    private $airline       = [];
    private $flight_number = [];
    private $travelers     = [];
    private $id_booking;
    private $pnr;
    private $ticket_number;
    private $booking_date;
    private $country;
    private $email;

    public function __construct($request)
    {
        $this->origin        = $request['origin'];
        $this->destination   = $request['destination'];
        $this->dep_date      = $request['dep_date'];
        $this->arr_date      = $request['arr_date'];
        $this->airline       = $request['airline'];
        $this->flight_number = $request['flight_number'];
        $this->travelers     = $request['travelers'];
        $this->id_booking    = $request['id_booking'];
        $this->pnr           = $request['pnr'];
        $this->ticket_number = $request['ticket_number'];
        $this->booking_date  = $request['booking_date'];
        $this->country       = $request['country'];
        $this->email         = $request['email'];
        $this->param         = 'api_token';
        $this->token         = Config::get('suppliers')[api::$query->query['provider']]['checkin_token'];
        $this->set_xml();
//        $this->http_header[] = 'expectedTestResult: success'; // "success" or "failed" (used for testing)
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
        foreach ($this->dep_date as &$value) {
            foreach ($value as &$val) {
                $val = (new DateTime($val))->format('Y-m-d H:i:s');
            }
            unset($val);
        }
        unset($value);
        foreach ($this->arr_date as &$value) {
            foreach ($value as &$val) {
                $val = (new DateTime($val))->format('Y-m-d H:i:s');
            }
            unset($val);
        }
        unset($value);
    }

    private function set_destination()
    {
        foreach ($this->origin as $fkey => $value) {
            foreach ($value as $skey => $val) {
                $this->request['journeys'][] = [
                    'airline'           => $this->airline[$fkey][$skey],
                    'departure_airport' => $this->origin[$fkey][$skey],
                    'arrival_airport'   => $this->destination[$fkey][$skey],
                    'departure_date'    => $this->dep_date[$fkey][$skey],
                    'arrival_date'      => $this->arr_date[$fkey][$skey],
                    'flight_number'     => $this->flight_number[$fkey][$skey],
                    'id_booking'        => $this->id_booking,
                    'pnr'               => $this->pnr,
                    'ticket_number'     => $this->ticket_number,
                ];
            }
        }
    }

    private function set_traveler()
    {
        foreach ($this->travelers as $traveler) {
            $this->request['passengers'][] = [
                'name'          => $traveler['name'],
                'last_name'     => $traveler['last_name'],
//                'gender'        => $traveler['gender'],
//                'date_of_birth' => $traveler['date_of_birth'],
//                'nationality'   => $traveler['nationality'],
                'document'      => [
                    'type'        => $traveler['type'],
                    'number'      => $traveler['number'],
                    'expire_date' => $traveler['expire_date'],
                    'country'     => $traveler['nationality'],
                ],
            ];
        }
    }

    private function set_options()
    {
        $this->request = [
            'journeys'      => [],
            'passengers'    => [],
            'brand'         => 'SKYTOURS',
            'lang'          => 'en_GB',
            'country'       => $this->country,
            'booking_date'  => $this->booking_date,
            'booking_email' => $this->email,
            'email'         => $this->email,
        ];
    }
}

class airobotAPIcheckincreateResult
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
