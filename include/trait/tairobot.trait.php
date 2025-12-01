<?php
trait tairobot
{
    private static function checkin_airlines($arr)
    {
        foreach ($arr as $value) {
            foreach ($value as $code) {
                $rs = OnDemandDb::CacheExecute('main',3600,"SELECT * FROM `airobot_airlines` WHERE `code` = '$code' AND `checkin`");
                if (is_object($rs)) {
                    if ($rs->RowCount()) {
                        $rs->Close();
                        return true;
                    }
                    $rs->Close();
                }
            }
        }
        return false;
    }

    private static function checkin_airline($code)
    {
        $rs = OnDemandDb::CacheExecute('main',3600,"SELECT * FROM `airobot_airlines` WHERE `code` = '$code' AND `checkin`");
        if (is_object($rs)) {
            if ($rs->RowCount()) {
                $rs->Close();
                return true;
            }
            $rs->Close();
        }
        return false;
    }

    private static function checkin_price($code)
    {
        $field = [];
        $rs    = OnDemandDb::CacheExecute('main',3600,"SELECT `checkin_amount`,`currency` FROM `airobot_airlines` WHERE `code` = '$code'");
        if (is_object($rs)) {
            if ($rs->RowCount()) {
                $field = $rs->fields;
            }
            $rs->Close();
        }
        return $field;
    }

    private static function get_reservation($id,$columns = '*',$table = 'flight_reservation')
    {
        $rs = OnDemandDb::Execute('main',"SELECT $columns FROM `$table` WHERE `id` = $id");
        if (is_object($rs)) {
            if ($rs->RowCount()) {
                $rs->MoveFirst();
                $from = $rs->fields;
            }
            $rs->Close();
        }
        return $from ?? [];
    }

    private static function is_pdf($data) {
        $PDF_MAGIC = "%PDF-";
        return substr($data,0,strlen($PDF_MAGIC)) === $PDF_MAGIC;
    }
}
