<?php
class checkin_delete
{
    use tairobot;

    public static function exec()
    {
        $reservation     = self::get_reservation(api::$query->query['id_order'],'`airobot_checkin`');
        $airobot_checkin = json_decode($reservation['airobot_checkin'],true);
        foreach ($airobot_checkin as $val) {
            if ($val['status'] === 'success') {
                $request = ['request_id' => $val['request_id']];
                $cAirobot = new airobotAPIcheckindelete($request);
                Logger::save_buffer('airobot checkin delete request',$cAirobot->xml,'ancillary');
                $cAirobot->request();
                Logger::save_buffer('airobot checkin delete response',$cAirobot->data,'ancillary');
                airobotAPIcheckindeleteResult::parse($cAirobot->data);
                if (empty(airobotAPIcheckindeleteResult::$Result) || isset(airobotAPIcheckindeleteResult::$Result['Fault'])) {
                    airobotCore::$result[] = [
                        'status'  => 'failed',
                        'message' => (empty(airobotAPIcheckindeleteResult::$Result)) ? 'STATUS_0002' : airobotAPIcheckindeleteResult::$Result['Fault']['faultstring'],
                    ];
                } else {
                    airobotCore::$result[] = [
                        'status'     => 'success',
                        'request_id' => airobotAPIcheckindeleteResult::$Result['request_id'],
                    ];
                }
            }
        }
    }
}
