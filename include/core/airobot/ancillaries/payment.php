<?php
$airobot_request_id = $REQUEST['airobot_request_id'] ?? $_REQUEST['request_id'] ?? '';
$airobot_uuid       = $params['airobot_uuid'] ?? $_REQUEST['airobot_uuid'] ?? '';
$master             = $master         ?? 'militaryfares';
$record_locator     = $record_locator ?? '';
$email              = $email          ?? '';
if (!empty($airobot_request_id) && !empty($airobot_uuid)) {
    $airobot_server = get_xmlapi_server($airobot_uuid,$master);
    $request_data   = Xmlapi::get('airobot',$airobot_server,$airobot_uuid);
    $travelers      = $request_data['ancillaries']['passengers'];
    foreach ($request_data['ancillaries']['seat'] as $val) {
        Util::$needle  = $val['journey'];
        Util::$key     = 'id';
        $filter_result = array_filter($request_data['ancillaries']['journeys'],'Util::_filter');
        $journey       = current($filter_result);
        Util::$needle  = $val['passenger'];
        Util::$key     = 'id';
        $filter_result = array_filter($travelers,'Util::_filter');
        $traveler      = current($filter_result);
        $journeys[]    = [
            'id'        => $journey['id'],
            'pnr'       => $record_locator,
            'email'     => $email,
            'name'      => $traveler['name'],
            'last_name' => $traveler['last_name'],
            'seats' => [              
                'passenger_id' => $val['passenger'],
                'seat'         => $val['seat'],
                'price'        => $val['price'],
            ]
        ];
    }

    $cAirobot = new airobotAPIpayment($airobot_request_id,$journeys,$travelers);
    $cAirobot->request();

    Logger::save_buffer('airobot payment request',$cAirobot->xml,'book');
    Logger::save_buffer('airobot payment response',$cAirobot->data,'book');

    Logger::save_log('airobot payment request',$cAirobot->xml,'ancillary','airobot',true);
    Logger::save_log('airobot payment response',$cAirobot->data,'ancillary','airobot',true);

    airobotAPIpaymentResult::parse($cAirobot->data);
    if (empty(airobotAPIpaymentResult::$Result) || isset(airobotAPIpaymentResult::$Result['Fault'])) {
        $airobot_error = (empty(airobotAPIpaymentResult::$Result)) ? ERROR_NOT_FOUND : airobotAPIpaymentResult::$Result['Fault']['faultstring'];
    } else {
        $result = airobotAPIpaymentResult::$Result;
    }
}
