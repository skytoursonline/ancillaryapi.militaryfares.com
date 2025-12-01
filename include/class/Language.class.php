<?php
class Language
{
    public static $effectiveLanguage = null;

    public static function get_effective_language($lang)
    {
        self::$effectiveLanguage = [];
        $rs = OnDemandDb::CacheExecute('main',86400,"SELECT * FROM `languages` WHERE `value` = '$lang'");
        if (is_object($rs)) {
            if ($rs->RowCount()) {
                self::$effectiveLanguage = $rs->fields;
            }
            $rs->Close();
        }
    }
}
