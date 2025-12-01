<?php
class airobotCore
{
    public static $result;

    public function exec()
    {
        $cls = api::$method->name.'_'.api::$method->action;
        $err = $cls::exec() ?? false;
        Logger::write_buffer('ancillary',api::$query->query['provider']);
        output::view(self::$result,$err);
    }
}
