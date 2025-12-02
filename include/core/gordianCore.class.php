<?php
class gordianCore
{
    public static $result;

    public function exec()
    {
        if (!Config::get('suppliers')[api::$query->query['provider']]['active']) output::view('STATUS_0006',true);
        $err = api::$method->name::exec() ?? false;
        Logger::write_buffer('ancillary',api::$query->query['provider']);
        output::view(self::$result,$err);
    }
}
