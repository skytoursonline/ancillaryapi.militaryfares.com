<?php
class checkin_passenger
{
    use tairobot;

    public static function exec()
    {
        $request = [
            'request_id'           => api::$query->query['request_id'],
            'passenger_journey_id' => api::$query->query['passenger_journey_id'],
        ];
        $cAirobot = new airobotAPIcheckinpassenger($request);
        Logger::save_buffer('airobot checkin passenger request',$cAirobot->xml,'ancillary');
        $cAirobot->request();
        Logger::save_buffer('airobot checkin passenger request',$cAirobot->data,'ancillary');
        if (empty($cAirobot->data)) {
            airobotCore::$result = [
                'status'  => 'failed',
                'message' => 'STATUS_0003',
            ];
            return;
        }
        if (self::is_pdf($cAirobot->data)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="filename.pdf"');
            die($cAirobot->data);
        }
        airobotAPIcheckinpassengerResult::parse($cAirobot->data);
        if (empty(airobotAPIcheckinpassengerResult::$Result) || isset(airobotAPIcheckinpassengerResult::$Result['Fault'])) {
            airobotCore::$result = [
                'status'  => 'failed',
                'message' => (empty(airobotAPIcheckinpassengerResult::$Result)) ? 'STATUS_0003' : airobotAPIcheckinpassengerResult::$Result['Fault']['faultstring'],
            ];
        } else {
            airobotCore::$result = [
                'status' => 'success',
                'data'   => airobotAPIcheckinpassengerResult::$Result,
            ];
        }
    }
}
