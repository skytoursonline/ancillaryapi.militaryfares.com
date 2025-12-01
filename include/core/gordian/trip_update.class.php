<?php
/*
provider=gordian&
method=trip_update&
id=1000000
*/

class trip_update
{
    use tgordian;

    public static function exec()
    {
        $id_order = api::$query->query['id'];
        $basket   = self::get_gordian_basket($id_order);
        $trip_id  = $basket['trip_id'];
        $frac     = Util::zero_decimal_currencies($basket['currency']);
        $products = json_decode($basket['products'],true);
        foreach ($products as $product) {
            $request[] = [
                'product_id'   => $product['product_id'],
                'passenger_id' => $product['passenger_id'],
                'quantity'     => (int)($product['quantity'] ?? 1),
                'markup'       => [
                    'amount'         => (int)bcmul($product['fee'],((in_array($basket['currency'],['KWD'])) ? 1000 : (($frac) ? 100 : 1)),0),
                    'decimal_places' => (in_array($basket['currency'],['KWD'])) ? 3 : (($frac) ? 2 : 0),
                    'currency'       => $basket['currency'],
                ],
            ];
        }
        $cGordian = new gordianAPIbasketadd($request,$trip_id);
        Logger::save_buffer('gordian basket add request',$cGordian->xml,'ancillary');
        $cGordian->request();
        Logger::save_buffer('gordian basket add response',$cGordian->data,'ancillary');
        gordianAPIbasketaddResult::parse($cGordian->data);
        if (empty(gordianAPIbasketaddResult::$Result) || isset(gordianAPIbasketaddResult::$Result['Fault'])) {
            gordianCore::$result = (empty(gordianAPIbasketaddResult::$Result)) ? 'UPDATE_OOO1' : gordianAPIbasketaddResult::$Result['Fault']['faultstring'];
            return true;
//            output::view($error,true);
        }
        $trip_state_hash = gordianAPIbasketaddResult::$Result['trip_state_hash'];
        OnDemandDb::Execute('main',"UPDATE `gordian_basket` SET `trip_state_hash` = '$trip_state_hash' WHERE `reservation_id` = $id_order");

        $cGordian = new gordianAPItripget($trip_id);
        Logger::save_buffer('gordian get request',$cGordian->xml,'ancillary');
        $cGordian->request();
        Logger::save_buffer('gordian get response',$cGordian->data,'ancillary');
        gordianAPItripgetResult::parse($cGordian->data);
        if (empty(gordianAPItripgetResult::$Result) || isset(gordianAPItripgetResult::$Result['Fault'])) {
            gordianCore::$result = (empty(gordianAPItripgetResult::$Result)) ? 'UPDATE_OOO2' : gordianAPItripgetResult::$Result['Fault']['faultstring'];
            return true;
//            output::view($error,true);
        }
        $passengers = gordianAPItripgetResult::$Result['passengers'];

        $v1 = $v2 = $v3 = [];
        foreach ($passengers as $val) {
            if ($val['type'] === 'adult') {
                $v1[] = $val;
            } elseif ($val['type'] === 'child') {
                $v2[] = $val;
            } elseif ($val['type'] === 'infant') {
                $v3[] = $val;
            }
        }
        $passengers = [...$v1,...$v2,...$v3]; // array_merge($v1,$v2,$v3);
        unset($v1,$v2,$v3);

        $reservation = self::get_reservation($id_order);
        $firstname   = unserialize($reservation['firstname']);
        $lastname    = unserialize($reservation['lastname']);
        $dateofbirth = unserialize($reservation['dateofbirth']);
        $adult       = $reservation['adult'] + $reservation['senior'] + $reservation['youth'];
        $request     = [];
        for ($i = 0, $j = 1, $k = 0; $i < $adult; $i++, $j++, $k++) {
            $request['travelers'][] = [
                'passenger_id'  => $passengers[$k]['passenger_id'],
                'date_of_birth' => (new DateTime($dateofbirth[$j]))->format('Y-m-d'),
                'first_names'   => $firstname[$j],
                'surname'       => $lastname[$j],
            ];
        }
        for ($i = 0; $i < $reservation['child']; $i++, $j++, $k++) {
            $request['travelers'][] = [
                'passenger_id'  => $passengers[$k]['passenger_id'],
                'date_of_birth' => (new DateTime($dateofbirth[$j]))->format('Y-m-d'),
                'first_names'   => $firstname[$j],
                'surname'       => $lastname[$j],
            ];
        }
        for ($i = 0, $l = 0; $i < $reservation['infant']; $i++, $j++, $k++, $l++) {
            $request['travelers'][] = [
                'passenger_id'  => $passengers[$k]['passenger_id'],
                'date_of_birth' => (new DateTime($dateofbirth[$j]))->format('Y-m-d'),
                'first_names'   => $firstname[$j],
                'surname'       => $lastname[$j],
            ];
        }
        $cGordian = new gordianAPItripupdate($request,$trip_id);
        Logger::save_buffer('gordian update request',$cGordian->xml,'ancillary');
        $cGordian->request();
        Logger::save_buffer('gordian update response',$cGordian->data,'ancillary');
        gordianAPItripupdateResult::parse($cGordian->data);
        if (empty(gordianAPItripupdateResult::$Result) || isset(gordianAPItripupdateResult::$Result['Fault'])) {
            gordianCore::$result = (empty(gordianAPItripupdateResult::$Result)) ? 'UPDATE_OOO3' : gordianAPItripupdateResult::$Result['Fault']['faultstring'];
            return true;
//            output::view($error,true);
        }
        gordianCore::$result = gordianAPItripupdateResult::$Result;
        return false;
    }
}
