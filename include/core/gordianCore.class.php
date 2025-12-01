<?php
class gordianCore
{
    public static $result;

    public function exec()
    {
        $err = api::$method->name::exec() ?? false;
        Logger::write_buffer('ancillary',api::$query->query['provider']);
        output::view(self::$result,$err);
    }
}
