<?php
class basket_get
{
    use tgordian;

    public static function exec()
    {
        $id_order = api::$query->query['id'];
        $basket   = self::get_gordian_basket($id_order);
        if ($trip_id = $basket['trip_id']) {
            $cGordian = new gordianAPIbasketget($trip_id);
            Logger::save_buffer('gordian basket get request',$cGordian->xml,'ancillary');
            $cGordian->request();
            Logger::save_buffer('gordian basket get response',$cGordian->data,'ancillary');
            gordianAPIbasketgetResult::parse($cGordian->data);
            if (empty(gordianAPIbasketgetResult::$Result) || isset(gordianAPIbasketgetResult::$Result['Fault'])) {
                $error = (empty(gordianAPIbasketgetResult::$Result)) ? 'BASKET_0003' : gordianAPIbasketgetResult::$Result['Fault']['faultstring'];
                output::view($error,true);
            }
            gordianCore::$result = gordianAPIbasketgetResult::$Result;
            return;
        }
        output::view('BASKET_0002',true,'trip_id');
    }
}
