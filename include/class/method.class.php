<?php
class method
{
    public $name;
    public $action;

    private static $list = [
        'trip_create',
        'add',
        'trip_update',
        'trip_get',
        'book',
        'cancel',
        'cancel_confirm',
        'cancel_get',
        'subscribe',
        'notification',
        'search',
        'basket_check',
        'basket_get',
        'trip_check',
        'checkin',
    ];
    private static $list_action = [
        'check',
        'create',
        'delete',
        'notification',
        'passenger',
        'status',
        'update',
    ];

    public function validate()
    {
        if (!empty($_REQUEST['method'])) {
            $this->name   = strtolower($_REQUEST['method']);
            if (in_array($this->name,self::$list)) {
                api::$write_sql = true;
                if (!empty($_REQUEST['action'])) {
                    $this->action = strtolower($_REQUEST['action']);
                    if (in_array($this->action,self::$list_action)) {
                        return true;
                    }
                    output::view('METHOD_0003',true);
                } else {
                    return true;
                }
            }
            output::view('METHOD_0002',true);
        }
        output::view('METHOD_0001',true);
    }
}
