<?php
$airobot_request_id = $REQUEST['airobot_request_id'] ?? $_REQUEST['request_id'] ?? '';
$seats_pass         = $seats_pass  ?? [];
$seats_price        = $seats_price ?? 0;
$total_price        = $total_price ?? 0;
if (!empty($airobot_request_id)) {
    $cAirobot = new airobotAPIancillarystatus($airobot_request_id);
    $cAirobot->request();

    Logger::save_buffer('airobot ancillary status request',$cAirobot->xml,'book');
    Logger::save_buffer('airobot ancillary status response',$cAirobot->data,'book');

    Logger::save_log('airobot ancillary status request',$cAirobot->xml,'ancillary','airobot',true);
    Logger::save_log('airobot ancillary status response',$cAirobot->data,'ancillary','airobot',true);

    airobotAPIancillarystatusResult::parse($cAirobot->data);
    if (empty(airobotAPIancillarystatusResult::$Result) || isset(airobotAPIancillarystatusResult::$Result['Fault'])) {
        $airobot_error = (empty(airobotAPIancillarystatusResult::$Result)) ? ERROR_NOT_FOUND : airobotAPIancillarystatusResult::$Result['Fault']['faultstring'];
    } else {
        $result     = airobotAPIancillarystatusResult::$Result;
        $journeys   = $result['ancillaries']['journeys'];
        $passengers = $result['ancillaries']['passengers'];
        foreach ($result['ancillaries']['seat'] as $val) {
            Util::$needle  = $val['journey'];
            Util::$key     = 'id';
            $filter_result = array_filter($journeys,'Util::_filter');
            $journey       = current($filter_result);
            Util::$needle  = $val['passenger'];
            Util::$key     = 'id';
            $filter_result = array_filter($passengers,'Util::_filter');
            $passenger     = current($filter_result);
            $seats_pass[]  = [
                'journey'   => $journey,
                'passenger' => $passenger,
                'seat'      => $val['seat'],
                'price'     => $val['price'],
            ];
            $seats_price += $val['price'];
        }
        $seats_price    = round(Currency::convert($result['ancillaries']['currency'],$currency,$seats_price),Currency::$effectiveCurrency['decimal']);
        $airobot_uuid   = uuid_v4();
        $airobot_server = get_xmlapi_server($airobot_uuid,$master);
        Xmlapi::save('airobot',$result,$airobot_server,$airobot_uuid);
    }
}
$total_price += $seats_price;
