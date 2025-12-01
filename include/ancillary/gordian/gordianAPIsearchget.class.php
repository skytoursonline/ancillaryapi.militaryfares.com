<?php
class gordianAPIsearchget extends gordianAPI
{
    protected $url_request = 'trip/{tripID}/search/{searchID}';

    public function __construct($search_id,$trip_access_token,$trip_id)
    {
        $this->method      = 'get';
        $this->auth_header = ($trip_access_token) ? ['Authorization: Bearer '.$trip_access_token] : ['Authorization: Basic '.base64_encode(Config::get('suppliers')[api::$query->query['provider']]['apikey'])];
        $this->url_request = str_replace(['{tripID}','{searchID}'],[$trip_id,$search_id],$this->url_request);
        gordianAPIsearchgetResult::$tripID = $trip_id;
    }
}

class gordianAPIsearchgetResult
{
    public static $Result   = [];
    public static $Baggage  = [];
    public static $Seat     = [];
    public static $Seatmaps = [];
    public static $tripID   = null;

    private static $taxes;
    private static $direction;

    public static function parse($response,$taxes)
    {
        self::$Result    = [];
        self::$Baggage   = [];
        self::$Seat      = [];
        self::$Seatmaps  = [];
        self::$taxes     = $taxes;
        self::$direction = [];

        $result = json_decode($response,true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if ((isset($result['status']) && $result['status'] === 'failed') || isset($result['errors'])) {
                self::$Result['Fault']['faultcode']   = -1;
                self::$Result['Fault']['faultstring'] = $result['details'] ?? $result['errors'][0]['description'];
                return;
            }
            self::$Result = $result;
            if ($result['status'] === 'success') {
                foreach (self::$Result['results']['itineraries'][0]['journeys'] as $i => $journey) {
                    self::$direction[$journey['journey_id']] = (($i) ? 'in' : 'out').'bound';
                }
                if (isset(self::$Result['results']['products']['bag'])) {
                    self::$Baggage = self::baggage(self::$Result['results']['products']['bag']);
                }
                if (isset(self::$Result['results']['products']['seat'])) {
                    self::$Seat     = self::seat(self::$Result['results']['products']['seat']);
                    self::$Seatmaps = self::seatmaps(self::$Result['results']['seatmaps']);
                }
                self::$Result            = array_merge(self::$Baggage,self::$Seatmaps);
                self::$Result['status']  = $result['status'];
                self::$Result['trip_id'] = gordianAPIsearchgetResult::$tripID;
            }
        } else {
            self::$Result['Fault']['faultcode']   = json_last_error();
            self::$Result['Fault']['faultstring'] = json_last_error_msg();
        }
    }

    private static function baggage($obj)
    {
        $arr   = [];
        $i_out = 0;
        $i_in  = 0;
        foreach ($obj as $product_id => $product) {
            Util::$key    = 'journey_id';
            Util::$needle = $product['product_details']['journey_id'];
            $result       = array_filter(self::$Result['results']['itineraries'][0]['journeys'],'Util::_filter');
            $direction    = self::$direction[$product['product_details']['journey_id']];
            if ($direction === 'outbound') {
                $i = $i_out;
            }
            if ($direction === 'inbound') {
                $i = $i_in;
            }
            $arr['baggage']['passengers']   = self::$Result['passengers'];
            $arr['baggage'][$direction][$i] = [
                'product_id'       => $product['product_id'],
                'display_name'     => $product['product_details']['display_name'],
                'dimension_unit'   => $product['product_details']['dimension_unit'] ?? null,
                'total_dimensions' => $product['product_details']['total_dimensions'] ?? null,
                'weight'           => $product['product_details']['weight'],
                'weight_unit'      => $product['product_details']['weight_unit'],

            ];
            foreach ($product['price_and_availability'] as $passenger_id => $passenger) {
                foreach ($passenger as $key => $baggage) {
                    $decimal = $baggage['price']['total']['decimal_places'];
                    $total   = $baggage['price']['total']['amount'] / (($decimal) ? 100 : 1);
                    $fee     = (!self::$taxes) ? 0 : bcadd(round(Currency::convert(self::$taxes[0][0]['currency'],$baggage['price']['total']['currency'],self::$taxes[0][0]['baggage_amount']),$decimal),$total * self::$taxes[0][0]['baggage_percent'] / 100,$decimal);
                    $arr['baggage'][$direction][$i]['passengers'][$passenger_id][$key] = [
                        'commission'   => $baggage['commission']['total']['amount'] / (($baggage['commission']['total']['decimal_places']) ? 100 : 1),
                        'price'        => bcadd($total,$fee,$decimal),
                        'fee'          => $fee,
                        'currency'     => $baggage['price']['total']['currency'],
                        'quantity'     => $baggage['quantity'],
                        'unit'         => $baggage['unit'],
                    ];
                }
            }
            if ($direction === 'outbound') {
                $i_out++;
            }
            if ($direction === 'inbound') {
                $i_in++;
            }
        }
        return $arr;
    }

    private static function seat($obj)
    {
        $arr   = [];
        $i_out = 0;
        $i_in  = 0;
        foreach ($obj as $product_id => $product) {
            Util::$key    = 'segment_id';
            Util::$needle = $product['product_details']['segment_id'];
            foreach (self::$Result['results']['itineraries'][0]['journeys'] as $key => $journey) {
                $result = array_filter($journey['segments'],'Util::_filter');
                if (!empty($result)) {
                    break;
                }
            }
            $direction = (($key) ? 'in' : 'out').'bound';
            if ($direction === 'outbound') {
                $i = $i_out;
            }
            if ($direction === 'inbound') {
                $i = $i_in;
            }
            $arr['seat'][$direction][$i] = [
                'segment'         => current($result),
                'passengers'      => self::$Result['passengers'],
                'display_name'    => $product['display_name'],
                'product_id'      => $product['product_id'],
                'product_type'    => $product['product_type'],
                'product_details' => $product['product_details'],
            ];
            foreach ($product['price_and_availability'] as $passenger_id => $passenger) {
                if ($passenger['available']) {
                    $decimal                        = $passenger['price']['total']['decimal_places'];
                    $seat['commission']['amount']   = $passenger['commission']['total']['amount'] / (($passenger['commission']['total']['decimal_places']) ? 100 : 1);
                    $seat['commission']['currency'] = $passenger['commission']['total']['currency'];
                    $seat['price']['amount']        = $passenger['price']['total']['amount'] / (($decimal) ? 100 : 1);
                    $seat['price']['currency']      = $passenger['price']['total']['currency'];
                    $fee                            = (!self::$taxes) ? 0 : bcadd(round(Currency::convert(self::$taxes[0][0]['currency'],$seat['price']['currency'],self::$taxes[0][0]['seats_amount']),$decimal),$seat['price']['amount'] * self::$taxes[0][0]['seats_percent'] / 100,$decimal);
                    $seat['price']['amount']        = bcadd($seat['price']['amount'],$fee,$decimal);
                    $seat['price']['fee']           = $fee;
                    $seat['price']['currency']      = $passenger['price']['total']['currency'];
                    foreach ($arr['seat'][$direction][$i]['passengers'] as &$val) {
                        if ($val['passenger_id'] === $passenger_id) {
                            $val['products'] = $seat;
                            break;
                        }
                    }
                    unset($val);
                }
            }
            if ($direction === 'outbound') {
                $i_out++;
            }
            if ($direction === 'inbound') {
                $i_in++;
            }
        }
        return $arr;
    }

    private static function seatmaps($obj)
    {
        $arr = [];
        foreach ($obj as $segment) {
            Util::$key    = 'segment_id';
            Util::$needle = $segment['segment_id'];
            foreach (self::$Result['results']['itineraries'][0]['journeys'] as $key => $journey) {
                $result = array_filter($journey['segments'],'Util::_filter');
                if (!empty($result)) {
                    break;
                }
            }
            $direction = (($key) ? 'in' : 'out').'bound';
            if (empty($s) || $s !== $direction) {
                $i = 0;
            }
            $arr['seatmaps']['passengers']   = self::$Result['passengers'];
            $arr['seatmaps'][$direction][$i] = [
                'available'  => $segment['available'],
                'definition' => $segment['decks'][0]['compartments'][0]['definition'],
                'seat_rows'  => [],
            ];
            if ($segment['available']) {
                foreach ($segment['decks'][0]['compartments'][0]['seat_rows'] as $key_row => $rows) {
                    $arr['seatmaps'][$direction][$i]['seat_rows'][$key_row] = [
                        'row'        => $rows['row'],
                        'row_groups' => [],
                    ];
                    foreach ($rows['row_groups'] as $key_group => $groups) {
                        foreach ($groups as $key_seat => $seat) {
                            $arr['seatmaps'][$direction][$i]['seat_rows'][$key_row]['row_groups'][$key_group][$key_seat] = [
                                'bookable_seat'   => $seat['bookable_seat'],
                                'display_name'    => $seat['display_name'],
                                'product_id'      => $seat['product_id'] ?? null,
                                'characteristics' => $seat['characteristics'] ?? null,
                            ];
                            if ($seat['bookable_seat']) {
                                $prices = self::$Result['results']['products']['seat'][$seat['product_id']]['price_and_availability'];
                                foreach ($prices as $price) {
                                    if ($price['available']) {
                                        $decimal      = $price['price']['total']['decimal_places'];
                                        $total        = $price['price']['total']['amount'] / (($decimal) ? 100 : 1);
                                        $fee          = (!self::$taxes) ? 0 : bcadd(round(Currency::convert(self::$taxes[0][0]['currency'],$price['price']['total']['currency'],self::$taxes[0][0]['seats_amount']),$decimal),$total * self::$taxes[0][0]['seats_percent'] / 100,$decimal);
                                        $passenger_id = $price['passenger_id'];
                                        $arr['seatmaps'][$direction][$i]['seat_rows'][$key_row]['row_groups'][$key_group][$key_seat]['passengers'][$passenger_id] = [
                                            'commission' => $price['commission']['total']['amount'] / (($price['commission']['total']['decimal_places']) ? 100 : 1),
                                            'price'      => bcadd($total,$fee,$decimal),
                                            'fee'        => $fee,
                                            'currency'   => $price['price']['total']['currency'],
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $s = $direction;
            $i++;
        }
        return $arr;
    }
}
