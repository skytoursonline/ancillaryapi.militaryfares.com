<?php
class search
{
    use tgordian;

    public static function exec()
    {
        $id_order = api::$query->query['id'];
        $basket   = self::get_gordian_basket($id_order);
        $trip_id  = $basket['trip_id'];
        $cGordian = new gordianAPIsearch($trip_id);
        Logger::save_buffer('gordian search request',$cGordian->xml,'ancillary');
        $cGordian->request();
        Logger::save_buffer('gordian search response',$cGordian->data,'ancillary');
        gordianAPIsearchResult::parse($cGordian->data);
        if (empty(gordianAPIsearchResult::$Result) || isset(gordianAPIsearchResult::$Result['Fault'])) {
            gordianCore::$result = (empty(gordianAPIsearchResult::$Result)) ? 'SEARCH_0001' : gordianAPIsearchResult::$Result['Fault']['faultstring'];
            return true;
        }
        $allowed      = self::get_gordian();
        $reservation  = self::get_reservation($id_order);
        $airline_code = unserialize($reservation['airline_code']);
        $taxes        = null;
        foreach ($airline_code as $i => $val) {
            for ($j = 0; $j < count($val); $j++) {
                $taxes[$i][$j] = $allowed[$val[$j]];
            }
        }
        $result    = gordianAPIsearchResult::$Result;
        $search_id = $result['search_id'];
        $token     = null;
        $trip_id   = $result['trip_id'];
        $step      = 1;
        do {
            $cGordian = new gordianAPIsearchget($search_id,$token,$trip_id);
            Logger::save_buffer('gordian search request',$cGordian->xml,'ancillary');
            $cGordian->request();
            Logger::save_buffer('gordian search response',$cGordian->data,'ancillary');
            gordianAPIsearchgetResult::parse($cGordian->data,$taxes);
            if (empty(gordianAPIsearchgetResult::$Result) || isset(gordianAPIsearchgetResult::$Result['Fault'])) {
                gordianCore::$result = (empty(gordianAPIsearchgetResult::$Result)) ? 'CREATE_0003' : gordianAPIsearchgetResult::$Result['Fault']['faultstring'];
                return true;
            }
            gordianCore::$result = gordianAPIsearchgetResult::$Result;
            $status = gordianCore::$result['status'];
            $step++;
            if ($step > 20) {
                break;
            }
            sleep(1);
        } while ($status === 'in_progress');
    }
}
