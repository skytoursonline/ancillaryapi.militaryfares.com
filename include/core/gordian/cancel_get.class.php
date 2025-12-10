<?php
class cancel_get
{
    use tgordian;

    public static function exec()
    {
        $id_order = api::$query->query['id'];
        $basket   = self::get_gordian_basket($id_order);
        $trip_id  = $basket['trip_id'];
        $cGordian = new gordianAPIcancelget($trip_id);
        Logger::save_buffer('gordian cancel get request',$cGordian->xml,'ancillary');
        $cGordian->request();
        Logger::save_buffer('gordian cancel get response',$cGordian->data,'ancillary');
        gordianAPIcancelgetResult::parse($cGordian->data);
        if (empty(gordianAPIcancelgetResult::$Result) || isset(gordianAPIcancelgetResult::$Result['Fault'])) {
            gordianCore::$result = (empty(gordianAPIcancelgetResult::$Result)) ? 'CANCEL_0003' : gordianAPIcancelgetResult::$Result['Fault']['faultstring'];
            return true;
        }
        gordianCore::$result = gordianAPIcancelgetResult::$Result;
    }
}
