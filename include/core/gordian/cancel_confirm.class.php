<?php
class cancel_confirm
{
    use tgordian;

    public static function exec()
    {
        $id_order = api::$query->query['id'];
        $basket   = self::get_gordian_basket($id_order);
        $trip_id  = $basket['trip_id'];
        $cGordian = new gordianAPIcancelconfirm($trip_id);
        Logger::save_buffer('gordian cancel confirm request',$cGordian->xml,'ancillary');
        $cGordian->request();
        Logger::save_buffer('gordian cancel confirm response',$cGordian->data,'ancillary');
        gordianAPIcancelconfirmResult::parse($cGordian->data);
        if (empty(gordianAPIcancelconfirmResult::$Result) || isset(gordianAPIcancelconfirmResult::$Result['Fault'])) {
            $error = (empty(gordianAPIcancelconfirmResult::$Result)) ? 'CANCEL_0002' : gordianAPIcancelconfirmResult::$Result['Fault']['faultstring'];
            output::view($error,true);
        }
        gordianCore::$result = gordianAPIcancelconfirmResult::$Result;
    }
}
