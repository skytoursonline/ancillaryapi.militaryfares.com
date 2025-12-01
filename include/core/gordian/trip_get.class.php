<?php
class trip_get
{
    use tgordian;

    public static function exec()
    {
        $id_order = api::$query->query['id'];
        $basket   = self::get_gordian_basket($id_order);
        $trip_id  = $basket['trip_id'];
        $cGordian = new gordianAPItripget($trip_id);
        Logger::save_buffer('gordian get request',$cGordian->xml,'ancillary');
        $cGordian->request();
        Logger::save_buffer('gordian get response',$cGordian->data,'ancillary');
        gordianAPItripgetResult::parse($cGordian->data);
        if (empty(gordianAPItripgetResult::$Result) || isset(gordianAPItripgetResult::$Result['Fault'])) {
            $error = (empty(gordianAPItripgetResult::$Result)) ? 'UPDATE_OOO2' : gordianAPItripgetResult::$Result['Fault']['faultstring'];
            output::view($error,true);
        }
        gordianCore::$result = gordianAPItripgetResult::$Result;
    }
}
