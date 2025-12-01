<?php
class checkin_check
{
    use tairobot;

    public static function exec()
    {
        $reservation     = self::get_reservation(api::$query->query['id_order'],'`airobot_checkin`');
        $airobot_checkin = json_decode($reservation['airobot_checkin'], true);
        foreach ($airobot_checkin as $key => &$value) {
            foreach ($value as $key2 => &$val) {
                $last_key = array_key_last($val);
                $_        = &$val[$last_key];
                if ($_['code'] == 1) {
                    $request  = ['request_id' => $_['request_id']];
                    $cAirobot = new airobotAPIcheckinstatus($request);
                    Logger::save_buffer('airobot checkin status request',$cAirobot->xml,'ancillary');
                    $cAirobot->request();
                    Logger::save_buffer('airobot checkin status response',$cAirobot->data,'ancillary');
                    airobotAPIcheckinstatusResult::parse($cAirobot->data);
                    if (empty(airobotAPIcheckinstatusResult::$Result) || isset(airobotAPIcheckinstatusResult::$Result['Fault'])) {
                        airobotCore::$result[$key][$key2] = [
                            'status'  => 'failed',
                            'message' => (empty(airobotAPIcheckinstatusResult::$Result)) ? 'STATUS_0001' : airobotAPIcheckinstatusResult::$Result['Fault']['faultstring'],
                        ];
                    } else {
                        foreach (airobotAPIcheckinstatusResult::$Result['data']['passenger_journey'] as $k => $v) {
                            if ($v['status'] != $_['passenger_journey'][$k]['status']) {
                                $_['passenger_journey'][$k]['status'] = $v['status'];
                            }
                        }
                        airobotCore::$result[$key][$key2] = airobotAPIcheckinstatusResult::$Result;
                    }
                }
            }
            unset($val);
        }
        unset($value);
        $rec = ['airobot_checkin' => json_encode($airobot_checkin)];
        $rs  = OnDemandDb::Execute('main',"SELECT * FROM `flight_reservation` WHERE `id` = $id_order");
        $sql = OnDemandDb::get('main')->GetUpdateSQL($rs,$rec);
        if (!empty($sql)) {
            OnDemandDb::Execute('main',$sql);
        }
    }
}
