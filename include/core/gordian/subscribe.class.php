<?php
class subscribe
{
    use tgordian;

    public static function exec()
    {
        $url      = 'http://'.HOSTNAME.'/?provider=gordian&method=notification';
        $event    = api::$query->query['event'];
        $cGordian = new gordianAPIsubscribe($url,$event);
        Logger::save_buffer('gordian subscribe request',$cGordian->xml,'ancillary');
        $cGordian->request();
        Logger::save_buffer('gordian subscribe response',$cGordian->data,'ancillary');
        gordianAPIsubscribeResult::parse($cGordian->data);
        if (empty(gordianAPIsubscribeResult::$Result) || isset(gordianAPIsubscribeResult::$Result['Fault'])) {
            $error = (empty(gordianAPIsubscribeResult::$Result)) ? 'SUBSCRIBE_0001' : gordianAPIsubscribeResult::$Result['Fault']['faultstring'];
            output::view($error,true);
        }
        gordianCore::$result = gordianAPIsubscribeResult::$Result;
    }
}
