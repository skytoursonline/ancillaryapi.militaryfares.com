<?php
class api
{
    public static $write_sql = false;
    public static $method    = null;
    public static $query     = null;
    public static $lifetime  = '1800';

    public static function exec()
    {
        self::$method = new method();
        self::$method->validate();

        self::$query = new query();
        self::$query->validate(self::$method->name,self::$method->action ?? null);

        $mtd = self::$query->query['provider'].'Core';
        $cls = new $mtd();
        $cls->exec();
    }
}
