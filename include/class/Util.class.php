<?php
class Util
{
    public static $needle;
    public static $key;

    public static function get_suppliers()
    {
        $rs = OnDemandDb::CacheExecute('main',0,"SELECT * FROM `ancillary_suppliers`");
        if (is_object($rs)) {
            if ($rs->RowCount()) {
                $rs->MoveFirst();
                while (!$rs->EOF) {
                    $name = $rs->fields['name'];
                    $fields['suppliers'][$name] = [
                        'active' => (bool)$rs->fields['active'],
                        'url'    => $rs->fields['url'],
                    ];
                    if ($name === 'airobot') {
                        $fields['suppliers'][$name] += [
                            'ancillary_token' => $rs->fields['ancillary_token'],
                            'checkin_token'   => $rs->fields['checkin_token'],
                        ];
                    }
                    if ($name === 'gordian') {
                        $fields['suppliers'][$name] += [
                            'apikey' => $rs->fields['apikey'].':',
                        ];
                    }
                    $rs->MoveNext();
                }
            }
            $rs->Close();
        }
        return $fields ?? null;
    }

    public static function get_suppliers_affiliate()
    {
        $type = (api::$query->query['price_mode'] === 'hotel') ? 1 : 0;
        $rs   = OnDemandDb::Execute('main',"SELECT `service`,`active` FROM `xml_services_fee_hotels` WHERE `name_service` = '".api::$affiliate->name."' AND `lang` = '".api::$query->query['lang']."' AND `type` = '$type'");
        if (is_object($rs)) {
            if ($rs->RowCount()) {
                $rs->MoveFirst();
                while (!$rs->EOF) {
                    $_['suppliers'][$rs->fields['service']]['active'] = (bool)$rs->fields['active'];
                    $rs->MoveNext();
                }
            }
            $rs->Close();
        }
        if (!empty($_)) {
            Config::put($_);
        }
    }

    public static function remote($action,$service,$key,$life = null,$payload = null)
    {
        $post = [
            'api'        => 'ancillary',
            'type'       => 'cache',
            'action'     => $action,
            'xmlService' => $service,
            'cacheKey'   => $key,
            'lifeTime'   => $life,
        ];
        if ($payload) {
            $data = gzencode(addslashes(serialize($payload)),9);
        }
        $method  = ($action === 'get') ? 'get' : 'post';
        $options = [
            CURLOPT_TIMEOUT        => 90,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_URL            => self::get_server($key).'?'.http_build_query($post),
        ];
        if ($method === 'post') {
            $options += [
                CURLOPT_ENCODING   => '',
                CURLOPT_POST       => true,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/gzip',
                ],
            ];
        }
        $ch = curl_init();
        curl_setopt_array($ch,$options);
        $result = curl_exec($ch);
        curl_close($ch);

        return ($action === 'get') ? @unserialize($result) : $result;
    }

    public static function _filter($arr)
    {
        return ($arr[self::$key] == self::$needle);
    }

    public static function zero_decimal_currencies($currency)
    {
        $zero_decimal_currencies = ['BIF','CLP','DJF','GNF','ISK','JPY','KMF','KRW','PYG','RWF','UGX','UYI','VND','VUV','XAF','XOF','XPF'];
        return (in_array($currency,$zero_decimal_currencies)) ? 0 : 1;
    }

    private static function get_server($key)
    {
        $a    = [];
        $type = (defined('DEVEL')) ? 'devel' : 'live';
        $rs   = OnDemandDb::CacheExecute('main',300,"SELECT `url` FROM `xmlapi_servers` WHERE `active` AND `type` = '$type'");
        if (is_object($rs)) {
            if ($rs->RowCount()) {
                $rs->MoveFirst();
                while (!$rs->EOF) {
                    $a[] = $rs->fields['url'];
                    $rs->MoveNext();
                }
            }
            $rs->Close();
        }
        $n = crc32($key) % count($a);
        return $a[$n] ?? null;
    }
}
