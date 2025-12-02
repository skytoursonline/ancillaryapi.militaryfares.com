<?php
class basket_check
{
    use tgordian;

    public static function exec()
    {
        $id_order = api::$query->query['id'];
        $basket   = self::get_gordian_basket($id_order);
        if ($trip_id = $basket['trip_id']) {
            $cGordian = new gordianAPIbasketcheck($trip_id);
            Logger::save_buffer('gordian basket check request',$cGordian->xml,'ancillary');
            $cGordian->request();
            Logger::save_buffer('gordian basket check response',$cGordian->data,'ancillary');
            gordianAPIbasketcheckResult::parse($cGordian->data);
            if (empty(gordianAPIbasketcheckResult::$Result) || isset(gordianAPIbasketcheckResult::$Result['Fault'])) {
                $error = (empty(gordianAPIbasketcheckResult::$Result)) ? 'BASKET_0001' : gordianAPIbasketcheckResult::$Result['Fault']['faultstring'];
                output::view($error,true);
            }
            gordianCore::$result = gordianAPIbasketcheckResult::$Result;
            return;
        }
        output::view('BASKET_0002',true,'trip_id');
    }
}
