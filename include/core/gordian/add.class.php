<?php
class add
{
    use tgordian;

    public static function exec()
    {
        $id_order    = api::$query->query['id'];
        $trip_id     = api::$query->query['trip_id'];
        $products    = api::$query->query['products'];
        $price       = 0;
        $fee         = 0;
        $amount      = 0;
        $price_seat  = 0;
        $fee_seat    = 0;
        $amount_seat = 0;
        $price_bagg  = 0;
        $fee_bagg    = 0;
        $amount_bagg = 0;
        foreach ($products as &$product) {
            $product['fee']  ??= 0;
            $product['amount'] = $product['price'] - $product['fee'];
            $price            += $product['price'];
            $fee              += $product['fee'];
            $amount           += $product['amount'];
            if ($product['type'] === 'seat') {
                $price_seat  += $product['price'];
                $fee_seat    += $product['fee'];
                $amount_seat += $product['amount'];
            }
            if ($product['type'] === 'baggage') {
                $price_bagg  += $product['price'];
                $fee_bagg    += $product['fee'];
                $amount_bagg += $product['amount'];
            }
        }
        $course_to_usd = Currency::get_course(api::$query->query['curr'],'USD');
        OnDemandDb::Execute('main',"INSERT INTO `gordian_basket` (`reservation_id`,`trip_id`,`products`,`price`,`fee`,`amount`,`price_seat`,`fee_seat`,`amount_seat`,`price_baggage`,`fee_baggage`,`amount_baggage`,`currency`,`add_date`,`course_to_usd`) VALUES ($id_order,'$trip_id','".json_encode($products)."',$price,$fee,$amount,$price_seat,$fee_seat,$amount_seat,$price_bagg,$fee_bagg,$amount_bagg,'".api::$query->query['curr']."',NOW(),$course_to_usd)");
        gordianCore::$result = 'SUCCESS';
    }
}
