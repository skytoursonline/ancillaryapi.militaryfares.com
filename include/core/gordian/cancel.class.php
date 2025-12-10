<?php
class cancel
{
    use tgordian;

    public static function exec()
    {
        $id_order = api::$query->query['id'];
        $basket   = self::get_gordian_basket($id_order);
        $trip_id  = $basket['trip_id'];
        $products = json_decode($basket['basket_item_id'],true);
        $cGordian = new gordianAPIcancel($products,$trip_id);
        Logger::save_buffer('gordian cancel request',$cGordian->xml,'ancillary');
        $cGordian->request();
        Logger::save_buffer('gordian cancel response',$cGordian->data,'ancillary');
        gordianAPIcancelResult::parse($cGordian->data);
        if (empty(gordianAPIcancelResult::$Result) || isset(gordianAPIcancelResult::$Result['Fault'])) {
            gordianCore::$result = (empty(gordianAPIcancelResult::$Result)) ? 'CANCEL_0001' : gordianAPIcancelResult::$Result['Fault']['faultstring'];
            return true;
        }
        gordianCore::$result = gordianAPIcancelResult::$Result;
    }
}
