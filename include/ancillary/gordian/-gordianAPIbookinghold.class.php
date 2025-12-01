<?php
class gordianAPIbookinghold extends gordianAPI
{
    protected $url_request = 'trip/{tripID}/hold';

    private $contact_details = [];
    private $passengers      = [];
    private $tickets         = [];

    public function __construct($request,$trip_id)
    {
        $this->contact_details = $request['contact_details'];
        $this->passengers      = $request['passengers'];
        $this->tickets         = $request['tickets'];
        $this->auth_header     = ['Authorization: Basic '.base64_encode(Config::get('suppliers')[api::$query->query['provider']]['apikey'])];
        $this->url_request     = str_replace('{tripID}',$trip_id,$this->url_request);
        $this->set_xml();
    }

    private function set_xml()
    {
        $this->set_options();
        $this->set_tickets();
        $this->set_traveler();
        $this->xml = json_encode($this->request);
    }

    private function set_tickets()
    {
        foreach ($this->tickets as $ticket) {
            $this->request['tickets'][] = $ticket;
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
            'status'          => 'started',
//            'trip_state_hash' => null,
            'contact_details' => $this->contact_details,
            'passengers'      => [],
            'tickets'         => [],
        ];
    }
}

class gordianAPIbookingholdResult
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
