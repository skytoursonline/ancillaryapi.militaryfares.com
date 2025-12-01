<?php
class checkin_check
{
    use tairobot;

    public static function exec()
    {
        for ($i = 0, $count_i = count(api::$query->query['airline_code']); $i < $count_i; $i++) {
            for ($j = 0, $count_j = count(api::$query->query['airline_code'][$i]); $j < $count_j; $j++) {
                $amount = 0;
                airobotCore::$result[$i][$j] = [
                    'airline'  => api::$query->query['airline_code'][$i][$j],
                    'status'   => 'failed',
                    'amount'   => $amount,
                    'currency' => api::$query->query['curr'],
                    'message'  => 'Can not do a check-in for an airline '.api::$query->query['airline_code'][$i][$j].'. Airline not supported.',
                ];
                if (self::checkin_airline(api::$query->query['airline_code'][$i][$j])) {
                    if ($field = self::checkin_price(api::$query->query['airline_code'][$i][$j])) {
                        $amount = round(Currency::convert($field['currency'],$_REQUEST['curr'],$field['checkin_amount'] * api::$query->query['passengers']),2);
                    }
                    airobotCore::$result[$i][$j]['status']  = 'success';
                    airobotCore::$result[$i][$j]['amount']  = $amount;
                    airobotCore::$result[$i][$j]['message'] = null;
                }
            }
        }
        Logger::save_buffer('airobot checkin check',airobotCore::$result,'ancillary');
    }
}
