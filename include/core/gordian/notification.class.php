<?php
class notification
{
    use tgordian;

    public static function exec()
    {
        $post = @file_get_contents('php://input');
        Logger::save_buffer('gordian '.api::$method->name.' post',$post,'ancillary');
        if (empty($post)) {
            return false;
        }
        $post = json_decode($post,true);
        if (empty($post)) {
            return false;
        }

        $trip_id = $post['trip']['trip_id'];
        $rs = OnDemandDb::Execute('main',"SELECT `id`,`reservation_id` FROM `gordian_basket` WHERE `trip_id` = '$trip_id' ORDER BY `id` DESC LIMIT 1");
        if (is_object($rs)) {
            if ($rs->RowCount()) {
                $id = $rs->fields['reservation_id'];
            }
            $rs->Close();
        }
        $sql    = "UPDATE `gordian_basket` SET ";
        $orders = json_encode($post['trip']['orders']);
        $event  = $post['last_action']['last_event'] ?? $post['last_action'];
        if (!empty($post['last_action']['errors'])) {
            $error = $post['last_action']['errors'][0]['message'];
            $err[] = "$error<br>";
            $sql  .= "`error` = '$error',";
        } elseif (in_array($event['status'],['success','completed'])) {
            $sql .= "`confirmed` = 1,";
        } elseif (in_array($event['status'],['failure'])) {
            $error = $event['event'] ?? $event['name'];
            $err[] = "$error<br>";
            $sql  .= "`error` = '$error',";
        }
        $sql .= "`confirm_date` = NOW(),`orders` = '$orders' WHERE `trip_id` = '$trip_id'";
        OnDemandDb::Execute('main',$sql);

        foreach ($post['trip']['orders'] as $val) {
            if ($val['last_event']['status'] !== 'succeeded') {
                $err[] = "{$val['display_name']} {$val['status']}<br>";
            }
        }
        if (!empty($err)) {
            $message = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><center>
            <table align="center" border="0" cellpadding="2" cellspacing="0" min-height="300" style="border:solid 1px #dee9f3;font-size:13px;background:#f9f9f9;overflow:hidden;" width="620">
                <tr><td valign="top"><table align="center" border="0" cellpadding="2" cellspacing="3" width="579">
                    <tr><td><table border="0" cellpadding="3" cellspacing="1" width="571">
                        <tr><td align="left"><a href="http://www.militaryfares.com/" target="_blank"><img alt="militaryfares" border="0" src="https://militaryfares.com/images/logo-st.png" title="militaryfares" width="245"></a></td></tr>
                        <tr><td align="center" height="70"><font color="#0099cc" face="Arial" size="4"><b>Gordian Booking Error!</b></font></td></tr>
                        <tr>
                            <td align="left" height="110" valign="top">
                                <p><span style="font-family:Roboto,Work Sans,sans-serif;font-size:16px;">Order ID: </span><a href="https://cpx.militaryfares.com/admin/index.php?id='.$id.'&action=view" target="_blank">'.$id.'</a></p>
                                <p><span style="font-family:Roboto,Work Sans,sans-serif;font-size:16px;">Trip ID: </span><span style="font-size:medium;font-weight:700;">'.$trip_id.'</span></p>
                                <p><span style="font-family:Roboto,Work Sans,sans-serif;font-size:16px;">Error: </span><span style="font-size:medium;font-weight:700;">'.implode(', ',$err).'</span></p>
                            </td>
                        </tr>
                    </table></td></tr>
                </table></td></tr>
            </table>
            </center></body></html>';
            sendmail($message,['support@militaryfares.com','peer@brest.by','vitalykovalev@tut.by'],'Gordian booking Error!','support@militaryfares.com','militaryfares.com');
        }
        gordianCore::$result = 'SUCCESS';
    }
}
