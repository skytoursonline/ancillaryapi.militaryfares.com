<?php
$airobot_request_id   = null;
$dep_airport          = $dep_airport ?? $_REQUEST['dep_airport'] ?? '';
$arr_airport          = $arr_airport ?? $_REQUEST['arr_airport'] ?? '';
$dep_flight_date_time = $dep_flight_date_time ?? $_REQUEST['dep_flight_date_time'] ?? '';
$airline_code         = $airline_code ?? $_REQUEST['airline_code'] ?? '';
$flight_num           = $flight_num ?? $_REQUEST['flight_num'] ?? '';
$seats                = $seats ?? $_REQUEST['seats'] ?? 0;
if ($seats) {
    $request = [
        'origin'        => $dep_airport,
        'destination'   => $arr_airport,
        'outdate'       => $dep_flight_date_time,
        'airline'       => $airline_code,
        'flight_number' => $flight_num,
        'travelers'     => array_fill(0,$seats,['name' => '','last_name' => '']),
    ];
    $cAirobot = new airobotAPIancillarycreate($request);
    $cAirobot->request();

    Logger::save_buffer('airobot ancillary create request',$cAirobot->xml,'book');
    Logger::save_buffer('airobot ancillary create response',$cAirobot->data,'book');

    Logger::save_log('airobot ancillary create request',$cAirobot->xml,'ancillary','airobot',true);
    Logger::save_log('airobot ancillary create response',$cAirobot->data,'ancillary','airobot',true);

    airobotAPIancillarycreateResult::parse($cAirobot->data);
    if (empty(airobotAPIancillarycreateResult::$Result) || isset(airobotAPIancillarycreateResult::$Result['Fault'])) {
        $airobot_error = (empty(airobotAPIancillarycreateResult::$Result)) ? ERROR_NOT_FOUND : airobotAPIancillarycreateResult::$Result['Fault']['faultstring'];
    } else {
        $result             = airobotAPIancillarycreateResult::$Result;
        $airobot_request_id = $result['request_id'];
    }
}
