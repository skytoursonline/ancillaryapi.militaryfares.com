<?php
class Currency
{
    public static $effectiveCurrency = null;
    private static $rates = null;

    public static function get_effective_currency($curr)
    {
        self::$effectiveCurrency = [];
        $rs = OnDemandDb::CacheExecute('main',3600,"SELECT * FROM `currencies` WHERE `currency` = '$curr'");
        if (is_object($rs)) {
            if ($rs->RowCount()) {
                self::$effectiveCurrency = $rs->fields;
            }
            $rs->Close();
        }
    }

    public static function get_course($from,$to)
    {
        if (!self::$rates) {
            self::get_rates();
        }
        return (isset(self::$rates[$from][$to])) ? self::$rates[$from][$to] : 1;
    }

    public static function convert($from,$to,$amount)
    {
        return self::get_course($from,$to) * $amount;
    }

    public static function has($curr)
    {
        $from = 0;
        $rs   = OnDemandDb::CacheExecute('main',0,"SELECT `id` FROM `currencies` WHERE `currency` = '$curr' AND `active`");
        if (is_object($rs)) {
            $from = $rs->RowCount();
            $rs->Close();
        }
        return (bool)$from;
    }

    private static function get_rates()
    {
        self::$rates = [];
        if (file_exists(DATA_DIR.'rates.php')) {
            $rates       = require_once(DATA_DIR.'rates.php');
            self::$rates = json_decode($rates,true);
        } else {
            $rs = OnDemandDb::CacheExecute('main',3600,"SELECT * FROM `tourico_convert_str`");
            while (!$rs->EOF) {
                self::$rates[$rs->fields['Money']] = $rs->fields;
                $rs->MoveNext();
            }
            $rs->Close();
        }
    }
}
