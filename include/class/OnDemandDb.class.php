<?php
include_once(__DIR__.'/../../vendor/adodb/adodb-php/adodb-errorhandler.inc.php');

class OnDemandDb
{
    private static $creds              = [];
    private static $connections        = [];
    private static $counters           = [];
    private static $shutdownRegistered = false;

    public static function register($key,$creds,$type = 'mysqli',$flags = 0)
    {
        $db                      = ADONewConnection($type);
        $db->debug               = DEBUG_SQL;
        $db->clientFlags         = $flags;
        self::$creds[$key]       = $creds;
        self::$connections[$key] = $db;
        self::$counters[$key]    = [
            'cache'    => 0,
            'live'     => 0,
            'livelist' => [],
        ];
    }

    public static function get($key)
    {
        if (!isset(self::$connections[$key])) {
            die("Unknown connection '$key'");
        }
        $db = &self::$connections[$key];
        if ($db->IsConnected()) {
            return $db;
        }
        return self::init($key);
    }

    public static function Execute($key,$sql)
    {
        global $log_id;
        self::$counters[$key]['live']++;
        self::$counters[$key]['livelist'][] = $sql;
        if (!empty(api::$write_sql)) error_log("$log_id - [".date('Y-m-d H:i:s')."] $sql\n",3,LOG_DIR.'error/adodb.log');
//        error_log("live - [".date('Y-m-d H:i:s')."] $sql\n",3,LOG_DIR.'sql/'.$log_id);
        return self::get($key)->Execute($sql);
    }

    public static function CacheExecute($key,$ttl,$sql = null)
    {
        global $log_id;
        if ($sql === null && is_string($ttl)) {
            $sql = $ttl;
            $ttl = 600;
        }
        $data = self::get($key)->CacheExecute($ttl,$sql);
        if (!$data) {
            $data = self::Execute($key,$sql);
            if ($data) {
                $data = self::get($key)->_rs2rs($data);
            }
//            error_log("live after cache ($ttl) - [".date('Y-m-d H:i:s')."] $sql\n",3,LOG_DIR.'sql/'.$log_id);
        } else {
            self::$counters[$key]['cache']++;
//            error_log("cache ($ttl) - [".date('Y-m-d H:i:s')."] $sql\n",3,LOG_DIR.'sql/'.$log_id);
        }
        if (!empty(api::$write_sql)) error_log("$log_id - [".date('Y-m-d H:i:s')."] $sql\n",3,LOG_DIR.'error/adodb.log');
        return $data;
    }

    public static function GetInsertSQL($key,$rs,$rec)
    {
        return self::get($key)->GetInsertSQL($rs,$rec);
    }

    public static function GetUpdateSQL($key,$rs,$rec,$update = false)
    {
        return self::get($key)->GetUpdateSQL($rs,$rec,$update);
    }

    public static function report()
    {
        print_r(self::$counters);
    }

    public static function closeAll($key = false)
    {
        if ($key) {
            if (self::$connections[$key]->IsConnected()) {
                self::$connections[$key]->Close();
            }
        } else {
            foreach (self::$connections as $db) {
                if ($db->IsConnected()) {
                    $db->Close();
                }
            }
        }
    }

    private static function init($key)
    {
        list($host,$user,$pass,$name) = self::$creds[$key];
        $db = self::$connections[$key];
        if ($db->Connect($host,$user,$pass,$name)) {
            $db->SetFetchMode(ADODB_FETCH_ASSOC);
            $db->Execute("SET NAMES 'utf8'");
            if (!self::$shutdownRegistered) {
                self::$shutdownRegistered = true;
                register_shutdown_function(['OnDemandDb','closeAll']);
            }
        }
        return $db;
    }
}
