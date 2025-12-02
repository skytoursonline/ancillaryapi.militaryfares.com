<?php
class book
{
    use tgordian;

    public static function exec()
    {
        $id_order = api::$query->query['id'];
        file_get_contents('https://'.HOSTNAME."/?provider=gordian&method=basket_check&id=$id_order");
        $basket   = self::get_gordian_basket($id_order);
        $trip_id  = $basket['trip_id'];
        $cGordian = new gordianAPItripget($trip_id);
        Logger::save_buffer('gordian get request',$cGordian->xml,'ancillary');
        $cGordian->request();
        Logger::save_buffer('gordian get response',$cGordian->data,'ancillary');
        gordianAPItripgetResult::parse($cGordian->data);
        if (empty(gordianAPItripgetResult::$Result) || isset(gordianAPItripgetResult::$Result['Fault'])) {
            $error = (empty(gordianAPItripgetResult::$Result)) ? 'UPDATE_OOO2' : gordianAPItripgetResult::$Result['Fault']['faultstring'];
            output::view($error,true);
        }
        $today = new DateTime();
        foreach (gordianAPItripgetResult::$Result['basket'] as $val) {
            $tickets[] = $val['ticket_id'];
/*
            $valid_date = new DateTime($val['validity']['valid_until']);
            if ($valid_date < $today) {
                $error = "Item {$val['basket_item_id']} validity has expired";
                output::view($error,true);
            }
*/
        }
        $tickets    = array_unique($tickets);
        $passengers = gordianAPItripgetResult::$Result['passengers'];
        foreach ($passengers as &$val) {
            $val['metadata'] = ['order_id' => $id_order];
        }
        unset($val);
        foreach ($passengers as $x => $val) {
            if ($val['type'] === 'adult') {
                break;
            }
        }

        $reservation    = self::get_reservation($id_order);
        $record_locator = (!empty($reservation['p_record_locator'])) ? $reservation['p_record_locator'] : ((!empty($reservation['record_locator']) && strtolower($reservation['record_locator']) !== 'not booked') ? $reservation['record_locator'] : $id_order);
        $firstname      = unserialize($reservation['firstname']);
        $lastname       = unserialize($reservation['lastname']);
        $dateofbirth    = unserialize($reservation['dateofbirth']);
        $eticket        = (!empty($reservation['eticket'])) ? unserialize($reservation['eticket']) : null;
        $adult          = $reservation['adult'] + $reservation['senior'] + $reservation['youth'];
        $address        = [
            'city'             => $reservation['city'],
            'country'          => $reservation['country'],
            'postal_code'      => $reservation['zipcode'],
            'street_address_1' => $reservation['street'],
        ];
        $request = [
            'trip_state_hash' => $basket['trip_state_hash'],
            'contact_details' => [
                'contact_details_type' => 'passenger',
                'passenger_id'         => $passengers[$x]['passenger_id'],
                'email'                => 'tickets@militaryfares.com', // $reservation['email'],
                'phone_number'         => $reservation['areacode'].$reservation['phone'],
                'address'              => $address,
            ],
            'passengers'      => $passengers,
            'tickets'         => [],
            'payment_details' => [
                'payment_type' => 'gordian_settlement',
            ],
            'metadata' => [
                'order_id' => $id_order,
            ],
        ];
        foreach ($tickets as $ticket) {
            $request['tickets'][] = [
                'ticket_id'      => $ticket,
                'access_details' => [
                    'record_locator' => $record_locator,
                    'ticket_number'  => $eticket[1] ?? '',
                ],
                'status' => 'booked',
            ];
        }
        $cGordian = new gordianAPIbooking($request,$trip_id);
        Logger::save_buffer('gordian booking request',$cGordian->xml,'ancillary');
        $cGordian->request();
        Logger::save_buffer('gordian booking response',$cGordian->data,'ancillary');
        gordianAPIbookingResult::parse($cGordian->data);
        if (empty(gordianAPIbookingResult::$Result) || isset(gordianAPIbookingResult::$Result['Fault'])) {
            $error = (empty(gordianAPIbookingResult::$Result)) ? 'BOOK_0001' : gordianAPIbookingResult::$Result['Fault']['faultstring'];
            output::view($error,true);
        }
        gordianCore::$result = gordianAPIbookingResult::$Result;
    }
}
