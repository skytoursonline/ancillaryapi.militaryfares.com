<?php
class checkin_create
{
    use tairobot;

    public static function exec()
    {
        $today               = new DateTime();
        $reservation         = self::get_reservation(api::$query->query['id_order']);
        $dep_airport         = unserialize($reservation['dep_airport']);
        $arr_airport         = unserialize($reservation['arr_airport']);
        $airline_code        = unserialize($reservation['airline_code']);
        $flight_num          = unserialize($reservation['flight_num']);
        $dep_flight_date     = unserialize($reservation['dep_flight_date']);
        $dep_flight_time     = unserialize($reservation['dep_flight_time']);
        $arr_flight_date     = unserialize($reservation['arr_flight_date']);
        $arr_flight_time     = unserialize($reservation['arr_flight_time']);
        $firstname           = unserialize($reservation['firstname']);
        $lastname            = unserialize($reservation['lastname']);
        $gender              = unserialize($reservation['gender']);
        $dateofbirth         = unserialize($reservation['dateofbirth']);
        $nationality         = unserialize($reservation['nationality']);
        $number              = unserialize($reservation['id_number']);
        $identity            = unserialize($reservation['identity']);
        $arec_loc            = unserialize($reservation['a_record_locator']);
        airobotCore::$result = json_decode($reservation['airobot_checkin'],true);
        airobotCore::$result = airobotCore::$result ?? [];
        foreach ($firstname as $j => $val) {
            $travelers[] = [
                'name'          => $firstname[$j],
                'last_name'     => $lastname[$j],
                'gender'        => strtoupper(substr($gender[$j],0,1)),
                'date_of_birth' => date('Y-m-d',strtotime($dateofbirth[$j])),
                'nationality'   => $nationality[$j],
                'type'          => 'passport',
                'number'        => $number[$j],
                'expire_date'   => date('Y-m-d',strtotime($identity[$j])),
                'country'       => $nationality[$j],
            ];
        }
        for ($i = 0, $count_i = count($dep_airport); $i < $count_i; $i++) {
            for ($j = 0, $count_j = count($dep_airport[$i]); $j < $count_j; $j++) {
                if (!empty(airobotCore::$result[$i][$j]) && in_array(end(airobotCore::$result[$i][$j])['code'],[-1,1])) {
                    continue;
                }
                $dep_date = DateTime::createFromFormat('dMHi',$dep_flight_date[$i][$j].$dep_flight_time[$i][$j]);
                if ($dep_date < $today) {
                    $dep_date->add(new DateInterval('P1Y'));
                }
                $arr_date = DateTime::createFromFormat('dMHi',$arr_flight_date[$i][$j].$arr_flight_time[$i][$j]);
                if ($arr_date < $today) {
                    $arr_date->add(new DateInterval('P1Y'));
                }
                $dep_flight_date_time[$i][$j] = $dep_date->format('Y-m-d H:i:s');
                $arr_flight_date_time[$i][$j] = $arr_date->format('Y-m-d H:i:s');
                if (self::checkin_airline($airline_code[$i][$j])) {
                    $request = [
                        'origin'        => [[$dep_airport[$i][$j]]],
                        'destination'   => [[$arr_airport[$i][$j]]],
                        'dep_date'      => [[$dep_flight_date_time[$i][$j]]],
                        'arr_date'      => [[$arr_flight_date_time[$i][$j]]],
                        'airline'       => [[$airline_code[$i][$j]]],
                        'flight_number' => [[$flight_num[$i][$j]]],
                        'travelers'     => $travelers,
                        'id_booking'    => $reservation['id'],
                        'pnr'           => (!empty($arec_loc['a_record_locator'][0])) ? implode('',$arec_loc) : ((!empty($reservation['record_locator']) && strtolower($reservation['record_locator']) !== 'not booked') ? $reservation['record_locator'] : $reservation['n_record_locator']),
                        'ticket_number' => $reservation['ticket_number'] ?? null,
                        'booking_date'  => $reservation['date_record'],
                        'country'       => $reservation['country'],
                        'email'         => $reservation['email'],
                    ];
                    if (empty($request['pnr'])) {
                        $pnrs = [];
                        if (is_string($reservation['auto_pnr'])) {
                            $reservation['auto_pnr'] = unserialize($reservation['auto_pnr']);
                        }
                        if (is_array($reservation['auto_pnr'])) {
                            foreach ($reservation['auto_pnr'] as $val) {
                                $pnrs = array_merge($pnrs,$val);
                            }
                        }
                        $pnrs = array_filter($pnrs);
                        if (!empty($pnrs)) {
                            $request['pnr'] = reset($pnrs);
                        }
                    }
                    $cAirobot = new airobotAPIcheckincreate($request);
                    Logger::save_buffer('airobot checkin create request',$cAirobot->xml,'ancillary');
                    $cAirobot->request();
                    Logger::save_buffer('airobot checkin create response',$cAirobot->data,'ancillary');
                    airobotAPIcheckincreateResult::parse($cAirobot->data);
                    if (empty(airobotAPIcheckincreateResult::$Result) || isset(airobotAPIcheckincreateResult::$Result['Fault'])) {
                        airobotCore::$result[$i][$j][] = [
                            'code'    => 0,
                            'status'  => 'failed',
                            'message' => (empty(airobotAPIcheckincreateResult::$Result)) ? 'STATUS_0005' : airobotAPIcheckincreateResult::$Result['Fault']['faultstring'],
                        ];
                    } else {
                        airobotCore::$result[$i][$j][] = [
                            'code'              => 1,
                            'status'            => 'success',
                            'request_id'        => airobotAPIcheckincreateResult::$Result['data']['request_id'],
                            'passenger_journey' => airobotAPIcheckincreateResult::$Result['data']['passenger_journey'],
                        ];
                    }
                } else {
                    airobotCore::$result[$i][$j][] = [
                        'code'    => -1,
                        'status'  => 'failed',
                        'message' => "Can not do a check-in for an airline {$airline_code[$i][$j]}. Airline not supported.",
                    ];
                }
            }
        }
        OnDemandDb::Execute('main',"UPDATE `flight_reservation` SET `airobot_checkin` = '".json_encode(airobotCore::$result)."' WHERE `id` = $id_order");
    }
}
