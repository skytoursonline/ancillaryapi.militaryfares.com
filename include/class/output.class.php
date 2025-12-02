<?php
class output
{
    private static $error = [
        'QUERY_0001'        => ['faultcode' => '0_0101','faultstring' => 'Query is required!'],
        'QUERY_0002'        => ['faultcode' => '0_0102','faultstring' => 'PARAM is required!'],
        'QUERY_0003'        => ['faultcode' => '0_0103','faultstring' => 'Dep airports is countable required!'],
        'QUERY_0004'        => ['faultcode' => '0_0104','faultstring' => 'Airline code is countable required!'],
        'METHOD_0001'       => ['faultcode' => '0_0201','faultstring' => 'Method is required!'],
        'METHOD_0002'       => ['faultcode' => '0_0202','faultstring' => 'Wrong method name!'],
        'METHOD_0003'       => ['faultcode' => '0_0203','faultstring' => 'Wrong action name!'],
        'CREATE_0001'       => ['faultcode' => '0_0001','faultstring' => 'Wrong airline!'],
        'CREATE_0002'       => ['faultcode' => '0_0001','faultstring' => 'Create a new trip wrong!'],
        'CREATE_0003'       => ['faultcode' => '0_0001','faultstring' => 'Get the search results wrong!'],
        'CREATE_0004'       => ['faultcode' => '0_0001','faultstring' => 'Get the search results many attempts!'],
        'SEARCH_0001'       => ['faultcode' => '1_0001','faultstring' => 'Start a search for flights or ancillaries wrong!'],
        'UPDATE_0001'       => ['faultcode' => '2_0001','faultstring' => 'Add products to the basket wrong!'],
        'UPDATE_0002'       => ['faultcode' => '2_0002','faultstring' => 'Get an existing trips information wrong!'],
        'UPDATE_0003'       => ['faultcode' => '2_0003','faultstring' => 'Update an existing trips information wrong!'],
        'BOOK_0001'         => ['faultcode' => '3_0001','faultstring' => 'Fulfill all the products in a trip basket wrong!'],
        'BASKET_0001'       => ['faultcode' => '4_0001','faultstring' => 'Fare check for all the products in a basket wrong!'],
        'BASKET_0002'       => ['faultcode' => '4_0002','faultstring' => 'PARAM wrong!'],
        'BASKET_0003'       => ['faultcode' => '4_0003','faultstring' => 'Get the contents of the basket wrong!'],
        'CHECK_0001'        => ['faultcode' => '5_0001','faultstring' => 'Check a trip wrong!'],
        'CANCEL_0001'       => ['faultcode' => '6_0001','faultstring' => 'Start a check for whether orders can be canceled and potential refunds wrong!'],
        'CANCEL_0002'       => ['faultcode' => '6_0002','faultstring' => 'Confirm the cancellation of a booking wrong!'],
        'CANCEL_0003'       => ['faultcode' => '6_0003','faultstring' => 'Get the details of a cancellation wrong!'],
        'SUBSCRIBE_0001'    => ['faultcode' => '7_0001','faultstring' => 'Create a new callback subscription wrong!'],
        'NOTIFICATION_0001' => ['faultcode' => '8_0001','faultstring' => 'Empty data'],
        'NOTIFICATION_0002' => ['faultcode' => '8_0002','faultstring' => 'Fault HMAC'],
        'STATUS_0001'       => ['faultcode' => '9_0001','faultstring' => 'Status check-in wrong!'],
        'STATUS_0002'       => ['faultcode' => '9_0002','faultstring' => 'Delete check-in wrong!'],
        'STATUS_0003'       => ['faultcode' => '9_0003','faultstring' => 'Passenger check-in wrong!'],
        'STATUS_0004'       => ['faultcode' => '9_0004','faultstring' => 'Update check-in wrong!'],
        'STATUS_0005'       => ['faultcode' => '9_0005','faultstring' => 'Create check-in wrong!'],
        'STATUS_0006'       => ['faultcode' => '9_0006','faultstring' => 'Suppliers disable!'],
    ];

    public static function view($data = null,$error = false,$param = null)
    {
        $return = [
            'timestamp' => date('Y-m-d\TH:i:s'),
            'method'    => api::$method->name,
            '_qa'       => api::$query->query ?? $_REQUEST,
            'status'    => (!$error) ? 'OK'  : 'ERR',
            'response'  => (!$error) ? $data : self::error($data,$param),
        ];
        header('Content-Type: application/json');
        die(json_encode($return,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
        flush();
    }

    private static function error($code = null,$param = null)
    {
        if (!is_array($code) && isset(self::$error[$code]) && $param) $code = str_replace('PARAM',ucfirst($param),self::$error[$code]['faultstring']);
        return (is_array($code)) ? $code : (self::$error[$code] ?? $code ?? 'Unknow Error!');
    }
}
