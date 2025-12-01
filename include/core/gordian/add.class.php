<?php
/*
provider=gordian&
method=add&
lang=en&
curr=USD&
id=6884341&
trip_id=4b039b72-e339-46d7-bce4-35cafca9c800&
products[0][product_id]=08ea3af2-1555-48f8-beb6-fb2c4fc67f1f&
products[0][passenger_id]=5278631c-2e58-429b-96e3-ba4d6c959ff6&
products[0][quantity]=1&
products[0][price]=18.30&
products[1][product_id]=e02a9eca-160b-4a44-a0df-621cd83882a4&
products[1][passenger_id]=5278631c-2e58-429b-96e3-ba4d6c959ff6&
products[1][quantity]=1&
products[1][price]=18.30&
products[2][product_id]=336a1abf-41d4-4e2d-bece-cc420b0f6ec9&
products[2][passenger_id]=5278631c-2e58-429b-96e3-ba4d6c959ff6&
products[2][quantity]=1&
products[2][price]=87.82&
products[3][product_id]=fa2a04e0-1406-4ac3-a62f-a11958a237ec&
products[3][passenger_id]=5278631c-2e58-429b-96e3-ba4d6c959ff6&
products[3][quantity]=1&
products[3][price]=87.82&

    id                        - OrderID received back from flight "store_reservation"

    products[0][product_id]   - seat/baggage to be booked for 1st pax
    products[0][passenger_id] - 1st pax code
    products[0][quantity]     - always=1 for seats, for baggage количество продукта
    products[0][price]        - price for product_id

    products[1][product_id]   - seat/baggage to be booked for 2nd pax
    products[1][passenger_id] - 2nd pax code
    products[1][quantity]     - always=1 for seats, for baggage количество продукта
    products[1][price]        - price for product_id

            reqUrl += `&products[${productIndex}][passenger_id]=${passId}`;
            reqUrl += `&products[${productIndex}][product_id]=${seat.seat.id}`;
            reqUrl += `&products[${productIndex}][quantity]=1`;
            reqUrl += `&products[${productIndex}][price]=${seat.seat["passengers"][passId].price}`;
            reqUrl += `&products[${productIndex}][type]=seat`;
            reqUrl += `&products[${productIndex}][passenger]=${seat.seat.passengerIndex}`;
            reqUrl += `&products[${productIndex}][name]=${seat.seat.display_name}`;
            reqUrl += `&products[${productIndex}][fee]=${seat.seat["passengers"][passId].fee}`;
            reqUrl += `&products[${productIndex}][direction]=${seat.outboundOrInbound}`;
            reqUrl += `&products[${productIndex}][segment]=${seat.segment}`;


{
"passenger_id":"1c7de5f6-a1b1-48ed-95e5-f615fd62a0b0",
"product_id":"2ea828b5-f806-4f71-ac0d-dd6a78c61169",
"quantity":"1",
"price":"0.00",
"type":"seat",
"passenger":"1",
"name":"Seat: 34F, AS 1150",
"fee":"0.00",
"direction":"0",
"segment":"0",
"amount":0
}

*/

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
//        $update = json_decode(file_get_contents(HOSTNAME."/?provider=gordian&method=trip_update&id=$id_order"),true);
        gordianCore::$result = 'SUCCESS';
    }
}
