<?php
class airobotCore
{
    public static $result;

    public function exec()
    {
        if (!Config::get('suppliers')[api::$query->query['provider']]['active']) output::view('STATUS_0006',true);
        $cls = api::$method->name.'_'.api::$method->action;
        $err = $cls::exec() ?? false;
        Logger::write_buffer('ancillary',api::$query->query['provider'].((api::$method->action === 'notification') ? '-notification' : ''));
        output::view(self::$result,$err);
    }
}
