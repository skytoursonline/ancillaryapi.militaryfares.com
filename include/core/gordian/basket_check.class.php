<?php
class basket_check
{
    use tgordian;

    public static function exec()
    {
        $id_order = api::$query->query['id'];
        $basket   = self::get_gordian_basket($id_order);
        $cGordian = new gordianAPIbasketcheck($trip_id);
        Logger::save_buffer('gordian basket check request',$cGordian->xml,'ancillary');
        $cGordian->request();
        Logger::save_buffer('gordian basket check response',$cGordian->data,'ancillary');
        gordianAPIbasketcheckResult::parse($cGordian->data);
        if (empty(gordianAPIbasketcheckResult::$Result) || isset(gordianAPIbasketcheckResult::$Result['Fault'])) {
            gordianCore::$result = (empty(gordianAPIbasketcheckResult::$Result)) ? 'BASKET_0001' : gordianAPIbasketcheckResult::$Result['Fault']['faultstring'];
            return true;
        }
        gordianCore::$result = gordianAPIbasketcheckResult::$Result;
    }
}
