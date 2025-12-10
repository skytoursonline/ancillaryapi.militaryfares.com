<?php
class basket_get
{
    use tgordian;

    public static function exec()
    {
        $id_order = api::$query->query['id'];
        $basket   = self::get_gordian_basket($id_order);
        $cGordian = new gordianAPIbasketget($trip_id);
        Logger::save_buffer('gordian basket get request',$cGordian->xml,'ancillary');
        $cGordian->request();
        Logger::save_buffer('gordian basket get response',$cGordian->data,'ancillary');
        gordianAPIbasketgetResult::parse($cGordian->data);
        if (empty(gordianAPIbasketgetResult::$Result) || isset(gordianAPIbasketgetResult::$Result['Fault'])) {
            gordianCore::$result = (empty(gordianAPIbasketgetResult::$Result)) ? 'BASKET_0003' : gordianAPIbasketgetResult::$Result['Fault']['faultstring'];
            return true;
        }
        gordianCore::$result = gordianAPIbasketgetResult::$Result;
    }
}
