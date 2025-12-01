<?php
class Logger
{
    public static $buffer = [];
    private static $dirs  = ['ancillary','debug'];
    private static $source;

    public static function write_buffer($group,$file = '',$is_hour = false,$hard_log = false)
    {
        if (_____LOG || $hard_log) {
            if (isset(self::$buffer[$group]) && is_array(self::$buffer[$group])) {
                self::tree($group,$file,$is_hour);
                if (file_put_contents(self::$source,self::$buffer[$group],FILE_APPEND) === false) {
                    $str = implode(self::$buffer[$group]);
                    $now = date('Y-m-d H:i:s');
                    error_log("[$now] : error writing to '".self::$source."':\n strings '$str'\n\n",3,ALL_ERROR_LOG_DEST);
                }
            }
        }
    }

    public static function save_buffer($message,$data,$group)
    {
        $data = (is_array($data) || is_object($data)) ? json_encode($data,JSON_FORCE_OBJECT) : str_replace(["\r\n","\n","\r"],'',$data);
        $now  = date('Y-m-d H:i:s');
        self::$buffer[$group][] = $_SERVER['REMOTE_ADDR']." - ".Config::get('log_id')." - [$now] \"$message\" $data\n";
    }

    public static function save_log($message,$data,$group = '',$file = '',$is_hour = false,$hard_log = false)
    {
        if (_____LOG || $hard_log) {
            self::tree($group,($group === 'debug') ? Config::get('log_id') : $file,$is_hour);
            self::write_string($message,$data);
        }
    }

    private static function tree($group,$file,$is_hour)
    {
        self::$source = LOG_DIR.date('Y-m-d');
        if (!is_dir(self::$source)) {
            mkdir(self::$source,0777);
            chmod(self::$source,0777);
            foreach (self::$dirs as $dir) {
                $dir = self::$source.'/'.$dir;
                if (!is_dir($dir)) {
                    mkdir($dir,0777);
                    chmod($dir,0777);
                }
            }
        }
        if (!empty($group)) {
            self::$source .= '/'.$group;
            if (in_array($group,['debug'])) {
                self::$source .= '/'.Config::get('log_id');
                if (!is_dir(self::$source)) {
                    mkdir(self::$source,0777);
                    chmod(self::$source,0777);
                }
            }
        }
        if (empty($file)) {
            $file = date('Y-m-d');
        }
        if ($is_hour) {
            $file .= '-'.date('H');
        }
        self::$source .= '/'.$file.'.log';
    }

    private static function write_string($message,$data)
    {
        $data = (is_array($data) || is_object($data)) ? json_encode($data,JSON_FORCE_OBJECT) : str_replace(["\r\n","\n","\r"],'',$data);
        $now  = date('Y-m-d H:i:s');
        $str  = (empty($data) && empty($message)) ? "\n" : $_SERVER['REMOTE_ADDR']." - ".Config::get('log_id')." - [$now] \"$message\" $data\n";
        if (file_put_contents(self::$source,$str,FILE_APPEND) === false) {
            error_log("[$now] : error writing to '".self::$source."' string '$str'\n\n",3,ALL_ERROR_LOG_DEST);
        }
    }
}
