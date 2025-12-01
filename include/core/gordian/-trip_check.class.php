<?php
class trip_check
{
    use tgordian;

    public static function exec()
    {
_d('TEST');
        $id_order = api::$query->query['id'];
        $basket   = self::get_gordian_basket($id_order);
        if ($trip_id = $basket['trip_id']) {
            $cGordian = new gordianAPItripcheck($trip_id);
            Logger::save_buffer('gordian trip check request',$cGordian->xml,'ancillary');
            $cGordian->request();
            Logger::save_buffer('gordian trip check response',$cGordian->data,'ancillary');
            gordianAPItripcheckResult::parse($cGordian->data);
            if (empty(gordianAPItripcheckResult::$Result) || isset(gordianAPItripcheckResult::$Result['Fault'])) {
                $error = (empty(gordianAPItripcheckResult::$Result)) ? 'CHECK_0001' : gordianAPItripcheckResult::$Result['Fault']['faultstring'];
                output::view($error,true);
            }
            gordianCore::$result = gordianAPItripcheckResult::$Result;
            return;
        }
        output::view('BASKET_0002',true,'trip_id');
    }
}
