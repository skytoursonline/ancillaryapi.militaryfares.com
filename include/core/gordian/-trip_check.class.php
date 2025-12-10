<?php
class trip_check
{
    use tgordian;

    public static function exec()
    {
        $id_order = api::$query->query['id'];
        $basket   = self::get_gordian_basket($id_order);
        $cGordian = new gordianAPItripcheck($trip_id);
        Logger::save_buffer('gordian trip check request',$cGordian->xml,'ancillary');
        $cGordian->request();
        Logger::save_buffer('gordian trip check response',$cGordian->data,'ancillary');
        gordianAPItripcheckResult::parse($cGordian->data);
        if (empty(gordianAPItripcheckResult::$Result) || isset(gordianAPItripcheckResult::$Result['Fault'])) {
            gordianCore::$result = (empty(gordianAPItripcheckResult::$Result)) ? 'CHECK_0001' : gordianAPItripcheckResult::$Result['Fault']['faultstring'];
            return true;
        }
        gordianCore::$result = gordianAPItripcheckResult::$Result;
    }
}
