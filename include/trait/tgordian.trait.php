<?php
trait tgordian
{
    private static function get_gordian()
    {
        $rs = OnDemandDb::Execute('main',"SELECT * FROM `gordian_airlines` WHERE `seats` OR `baggage`");
        if (is_object($rs)) {
            if ($rs->RowCount()) {
                $rs->MoveFirst();
                while (!$rs->EOF) {
                    $from[$rs->fields['code']] = $rs->fields;
                    $rs->MoveNext();
                }
            }
            $rs->Close();
        }
        return $from ?? [];
    }
    
    private static function get_gordian_basket($id)
    {
        $rs = OnDemandDb::Execute('main',"SELECT * FROM `gordian_basket` WHERE `reservation_id` = $id ORDER BY `id` DESC LIMIT 1");
        if (is_object($rs)) {
            if ($rs->RowCount()) {
                $rs->MoveFirst();
                while (!$rs->EOF) {
                    $from = $rs->fields;
                    $rs->MoveNext();
                }
            }
            $rs->Close();
        }
        return $from ?? [];
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
}
