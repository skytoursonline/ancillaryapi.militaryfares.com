<?php
/*
provider=gordian&
method=trip_create&
lang=en&
curr=USD&
adults=2&
children=0&
infants=0&
country=US&
dep_airport[0][0]=PEK&
dep_date[0][0]=03SEP&
dep_time[0][0]=0305&
airline[0][0]=KL&
flight_number[0][0]=892&
arr_airport[0][0]=AMS&
arr_date[0][0]=03SEP&
arr_time[0][0]=0730&
fare_class[0][0]=U&
fare_basis[0][0]=UM80BDNC&
dep_airport[0][1]=AMS&
dep_date[0][1]=03SEP&
dep_time[0][1]=0945&
airline[0][1]=KL&
flight_number[0][1]=1279&
arr_airport[0][1]=EDI&
arr_date[0][1]=03SEP&
arr_time[0][1]=1015&
fare_class[0][1]=Y&
fare_basis[0][1]=UM80BDNC
*/

class trip_create
{
    use tgordian;

    public static function exec()
    {
        $gordian_key         = sha1(http_build_query(api::$query->query));
        gordianCore::$result = Util::remote('get','gordian',$gordian_key,3600);
        if (empty(gordianCore::$result)) {
            $allowed = self::get_gordian();
            foreach (api::$query->query['airline'] as $_) {
                foreach ($_ as $__) {
                    if (!array_key_exists($__,$allowed)) {
                        output::view('CREATE_0001',true);
                    }
                }
            }
            $request = [
                'origin'        => api::$query->query['dep_airport'],
                'destination'   => api::$query->query['arr_airport'],
                'dep_date'      => null,
                'arr_date'      => null,
                'fare_basis'    => api::$query->query['fare_basis'],
                'fare_class'    => api::$query->query['fare_class'],
                'fare_family'   => null,
                'airline'       => api::$query->query['airline'],
                'flight_number' => api::$query->query['flight_number'],
                'travelers'     => null,
                'language'      => api::$query->query['lang'],
                'currency'      => api::$query->query['curr'],
                'country'       => api::$query->query['country'],
            ];
            for ($i = 0, $j = 1; $i < api::$query->query['adults']; $i++, $j++) {
                $request['travelers'][] = [
                    'passenger_id'   => 'pax-0'.$j,
                    'passenger_type' => 'adult',
                ];
            }
            for ($i = 0; $i < api::$query->query['children']; $i++, $j++) {
                $request['travelers'][] = [
                    'passenger_id'   => 'pax-0'.$j,
                    'passenger_type' => 'child',
                ];
            }
            for ($i = 0, $a = 1; $i < api::$query->query['infants']; $i++, $j++, $a++) {
                $request['travelers'][] = [
                    'passenger_id'   => 'pax-0'.$j,
                    'passenger_type' => 'infant',
                    'on_lap_of'      => 'pax-0'.$a,
                ];
            }
            $today = new DateTime();
            for ($i = 0, $count_i = count($request['origin']); $i < $count_i; $i++) {
                for ($j = 0, $count_j = count($request['origin'][$i]); $j < $count_j; $j++) {
                    $dep_date = DateTime::createFromFormat('dMHi',api::$query->query['dep_date'][$i][$j].api::$query->query['dep_time'][$i][$j]);
                    if ($dep_date < $today) {
                        $dep_date->add(new DateInterval('P1Y'));
                    }
                    $arr_date = DateTime::createFromFormat('dMHi',api::$query->query['arr_date'][$i][$j].api::$query->query['arr_time'][$i][$j]);
                    if ($arr_date < $today) {
                        $arr_date->add(new DateInterval('P1Y'));
                    }
                    $request['dep_date'][$i][$j] = $dep_date->format('Y-m-d\TH:i:s');
                    $request['arr_date'][$i][$j] = $arr_date->format('Y-m-d\TH:i:s');
                    if (empty($request['fare_basis'][$i])) {
                        $request['fare_basis'][$i] = [];
                    }
                    if (empty($request['fare_basis'][$i][$j])) {
                        $request['fare_basis'][$i][$j] = null;
                    }
                    $request['fare_family'][$i][$j] = null;
                    $taxes[$i][$j] = $allowed[$request['airline'][$i][$j]];
                }
            }
            $cGordian = new gordianAPItripcreate($request);
            Logger::save_buffer('gordian create request',$cGordian->xml,'ancillary');
            $cGordian->request();
            Logger::save_buffer('gordian create response',$cGordian->data,'ancillary');
            gordianAPItripcreateResult::parse($cGordian->data);
            if (empty(gordianAPItripcreateResult::$Result) || isset(gordianAPItripcreateResult::$Result['Fault'])) {
                $error = (empty(gordianAPItripcreateResult::$Result)) ? 'CREATE_0002' : gordianAPItripcreateResult::$Result['Fault']['faultstring'];
                output::view($error,true);
            }
            $result    = gordianAPItripcreateResult::$Result;
            $search_id = $result['search_id'];
            $token     = $result['trip_access_token'];
            $trip_id   = $result['trip_id'];
            $step      = 1;
            do {
                $cGordian = new gordianAPIsearchget($search_id,$token,$trip_id);
                Logger::save_buffer('gordian search request',$cGordian->xml,'ancillary');
                $cGordian->request();
                Logger::save_buffer('gordian search response',$cGordian->data,'ancillary');
                gordianAPIsearchgetResult::parse($cGordian->data,$taxes);
                if (empty(gordianAPIsearchgetResult::$Result) || isset(gordianAPIsearchgetResult::$Result['Fault'])) {
                    $error = (empty(gordianAPIsearchgetResult::$Result)) ? 'CREATE_0003' : gordianAPIsearchgetResult::$Result['Fault']['faultstring'];
                    output::view($error,true);
                }
                gordianCore::$result = gordianAPIsearchgetResult::$Result;
                $status = gordianCore::$result['status'];
                $step++;
                if ($step > 20) {
//                    output::view('CREATE_0004',true);
                    break;
                }
                sleep(1);
            } while ($status === 'in_progress');
            if ($status === 'success') {
                Util::remote('save','gordian',$gordian_key,3600,gordianCore::$result);
            }
        }
    }
}
