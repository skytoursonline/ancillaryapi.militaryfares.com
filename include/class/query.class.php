<?php
class query
{
    public $query;

    public function validate($method,$action = null)
    {
        if (empty($_REQUEST['provider'])) {
            output::view('QUERY_0002',true,'provider');
        }
        $method = 'validate_'.$method.(($action) ? '_'.$action : '');
        if (method_exists($this,$method)) {
            if (!$this->$method()) {
                output::view('QUERY_0001',true);
            }
        }
    }

    private function validate_checkin_check()
    {
        if (empty($_GET)) {
            return false;
        }

        if (!is_countable($_REQUEST['airline_code'])) {
            output::view('QUERY_0004',true);
        }

        $lang = strtolower($_REQUEST['lang'] ?? 'en');
        Language::get_effective_language($lang);

        $curr = strtoupper($_REQUEST['curr'] ?? 'USD');
        if (!Currency::has($curr)) {
            $curr = 'USD';
        }
        Currency::get_effective_currency($curr);

        $this->query = [
            'provider'     => $_REQUEST['provider'],
            'curr'         => $curr,
            'lang'         => $lang,
            'passenger'    => $_REQUEST['passenger'] ?? 1,
            'airline_code' => $_REQUEST['airline_code'],
        ];

        return true;
    }

    private function validate_checkin_status()
    {
        if (empty($_GET)) {
            return false;
        }

        if (empty($_REQUEST['order'])) {
            output::view('QUERY_0002',true,'id order');
        }

        $lang = strtolower($_REQUEST['lang'] ?? 'en');
        Language::get_effective_language($lang);

        $curr = strtoupper($_REQUEST['curr'] ?? 'USD');
        if (!Currency::has($curr)) {
            $curr = 'USD';
        }
        Currency::get_effective_currency($curr);

        $this->query = [
            'provider' => $_REQUEST['provider'],
            'curr'     => $curr,
            'lang'     => $lang,
            'id_order' => $_REQUEST['order'],
        ];

        return true;
    }

    private function validate_checkin_passenger()
    {
        if (empty($_GET)) {
            return false;
        }

        if (empty($_REQUEST['request_id'])) {
            output::view('QUERY_0002',true,'request id');
        }

        if (empty($_REQUEST['passenger_journey_id'])) {
            output::view('QUERY_0002',true,'passenger journey id');
        }

        $lang = strtolower($_REQUEST['lang'] ?? 'en');
        Language::get_effective_language($lang);

        $curr = strtoupper($_REQUEST['curr'] ?? 'USD');
        if (!Currency::has($curr)) {
            $curr = 'USD';
        }
        Currency::get_effective_currency($curr);

        $this->query = [
            'provider'             => $_REQUEST['provider'],
            'curr'                 => $curr,
            'lang'                 => $lang,
            'request_id'           => $_REQUEST['req'],
            'passenger_journey_id' => $_REQUEST['pass'],
        ];

        return true;
    }

    private function validate_checkin_delete()
    {
        if (empty($_GET)) {
            return false;
        }

        if (empty($_REQUEST['order'])) {
            output::view('QUERY_0002',true,'id order');
        }

        $lang = strtolower($_REQUEST['lang'] ?? 'en');
        Language::get_effective_language($lang);

        $curr = strtoupper($_REQUEST['curr'] ?? 'USD');
        if (!Currency::has($curr)) {
            $curr = 'USD';
        }
        Currency::get_effective_currency($curr);

        $this->query = [
            'provider' => $_REQUEST['provider'],
            'curr'     => $curr,
            'lang'     => $lang,
            'id_order' => $_REQUEST['order'],
        ];

        return true;
    }

    private function validate_checkin_update()
    {
        if (empty($_GET)) {
            return false;
        }

        if (empty($_REQUEST['order'])) {
            output::view('QUERY_0002',true,'id order');
        }

        $lang = strtolower($_REQUEST['lang'] ?? 'en');
        Language::get_effective_language($lang);

        $curr = strtoupper($_REQUEST['curr'] ?? 'USD');
        if (!Currency::has($curr)) {
            $curr = 'USD';
        }
        Currency::get_effective_currency($curr);

        $this->query = [
            'provider' => $_REQUEST['provider'],
            'curr'     => $curr,
            'lang'     => $lang,
            'id_order' => $_REQUEST['order'],
        ];

        return true;
    }

    private function validate_trip_create()
    {
        if (empty($_GET)) {
            return false;
        }

        if (!is_countable($_REQUEST['dep_airport'])) {
            output::view('QUERY_0003',true);
        }

        $lang = strtolower($_REQUEST['lang'] ?? 'en');
        Language::get_effective_language($lang);

        $curr = strtoupper($_REQUEST['curr'] ?? 'USD');
        if (!Currency::has($curr)) {
            $curr = 'USD';
        }
        Currency::get_effective_currency($curr);

        $this->query = [
            'provider'      => $_REQUEST['provider'],
            'dep_airport'   => $_REQUEST['dep_airport'],
            'arr_airport'   => $_REQUEST['arr_airport'],
            'fare_basis'    => $_REQUEST['fare_basis'],
            'fare_class'    => $_REQUEST['fare_class'],
            'airline'       => $_REQUEST['airline'],
            'curr'          => $curr,
            'lang'          => $lang,
            'flight_number' => $_REQUEST['flight_number'],
            'country'       => $_REQUEST['country'],
            'adults'        => $_REQUEST['adults'],
            'children'      => $_REQUEST['children'],
            'infants'       => $_REQUEST['infants'],
            'dep_date'      => $_REQUEST['dep_date'],
            'dep_time'      => $_REQUEST['dep_time'],
            'arr_date'      => $_REQUEST['arr_date'],
            'arr_time'      => $_REQUEST['arr_time'],
        ];

        return true;
    }

    private function validate_add()
    {
        if (empty($_GET)) {
            return false;
        }

        if (empty($_REQUEST['id'])) {
            output::view('QUERY_0002',true,'id');
        }

        if (empty($_REQUEST['trip_id'])) {
            output::view('QUERY_0002',true,'trip_id');
        }

        if (empty($_REQUEST['products'])) {
            output::view('QUERY_0002',true,'products');
        }

        $lang = strtolower($_REQUEST['lang'] ?? 'en');
        Language::get_effective_language($lang);

        $curr = strtoupper($_REQUEST['curr'] ?? 'USD');
        if (!Currency::has($curr)) {
            $curr = 'USD';
        }
        Currency::get_effective_currency($curr);

        $this->query = [
            'provider' => $_REQUEST['provider'],
            'id'       => $_REQUEST['id'],
            'trip_id'  => $_REQUEST['trip_id'],
            'products' => $_REQUEST['products'],
            'curr'     => $curr,
            'lang'     => $lang,
        ];

        return true;
    }

    private function validate_trip_update()
    {
        if (empty($_GET)) {
            return false;
        }

        if (empty($_REQUEST['id'])) {
            output::view('QUERY_0002',true,'id');
        }

        $this->query = [
            'provider' => $_REQUEST['provider'],
            'id'       => $_REQUEST['id'],
        ];

        return true;
    }

    private function validate_trip_get()
    {
        if (empty($_GET)) {
            return false;
        }

        if (empty($_REQUEST['id'])) {
            output::view('QUERY_0002',true,'id');
        }

        $this->query = [
            'provider' => $_REQUEST['provider'],
            'id'       => $_REQUEST['id'],
        ];

        return true;
    }

    private function validate_book()
    {
        if (empty($_GET)) {
            return false;
        }

        if (empty($_REQUEST['id'])) {
            output::view('QUERY_0002',true,'id');
        }

        $this->query = [
            'provider' => $_REQUEST['provider'],
            'id'       => $_REQUEST['id'],
        ];

        return true;
    }

    private function validate_basket_check()
    {
        if (empty($_GET)) {
            return false;
        }

        if (empty($_REQUEST['id'])) {
            output::view('QUERY_0002',true,'id');
        }

        $this->query = [
            'provider' => $_REQUEST['provider'],
            'id'       => $_REQUEST['id'],
        ];

        return true;
    }

    private function validate_basket_get()
    {
        if (empty($_GET)) {
            return false;
        }

        if (empty($_REQUEST['id'])) {
            output::view('QUERY_0002',true,'id');
        }

        $this->query = [
            'provider' => $_REQUEST['provider'],
            'id'       => $_REQUEST['id'],
        ];

        return true;
    }

    private function validate_trip_check()
    {
        if (empty($_GET)) {
            return false;
        }

        if (empty($_REQUEST['id'])) {
            output::view('QUERY_0002',true,'id');
        }

        $this->query = [
            'provider' => $_REQUEST['provider'],
            'id'       => $_REQUEST['id'],
        ];

        return true;
    }

    private function validate_cancel()
    {
        if (empty($_GET)) {
            return false;
        }

        if (empty($_REQUEST['id'])) {
            output::view('QUERY_0002',true,'id');
        }

        $this->query = [
            'provider' => $_REQUEST['provider'],
            'id'       => $_REQUEST['id'],
        ];

        return true;
    }

    private function validate_cancel_confirm()
    {
        if (empty($_GET)) {
            return false;
        }

        if (empty($_REQUEST['id'])) {
            output::view('QUERY_0002',true,'id');
        }

        $this->query = [
            'provider' => $_REQUEST['provider'],
            'id'       => $_REQUEST['id'],
        ];

        return true;
    }

    private function validate_cancel_get()
    {
        if (empty($_GET)) {
            return false;
        }

        if (empty($_REQUEST['id'])) {
            output::view('QUERY_0002',true,'id');
        }

        $this->query = [
            'provider' => $_REQUEST['provider'],
            'id'       => $_REQUEST['id'],
        ];

        return true;
    }

    private function validate_search()
    {
        if (empty($_GET)) {
            return false;
        }

        if (empty($_REQUEST['id'])) {
            output::view('QUERY_0002',true,'id');
        }

        $this->query = [
            'provider' => $_REQUEST['provider'],
            'id'       => $_REQUEST['id'],
        ];

        return true;
    }

    private function validate_subscribe()
    {   
        if (empty($_GET)) {
            return false;
        }

        $this->query = [
            'provider' => $_REQUEST['provider'],
            'event'    => $_REQUEST['event'] ?? 'fulfillment_completed',
        ];

        return true;
    }

    private function validate_notification()
    {   
        if (empty($_GET)) {
            return false;
        }

        $this->query = [
            'provider' => $_REQUEST['provider'],
        ];

        return true;
    }
}
