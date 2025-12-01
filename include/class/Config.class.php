<?php
class Config
{
    private static $config = [];
    private static $debug  = [];

    public static function set()
    {
        if (file_exists(SERVER_ROOT_DIR.'/.env') && !empty($ENVPARAMS = @parse_ini_file(SERVER_ROOT_DIR.'/.env',true))) {
            if (!empty($ENVPARAMS['ENV'])) {
                if ($ENVPARAMS['ENV'] === 'dev') {
                    define('DEVEL',true);
                }
            }
        }
        defined('DEVEL') || define('DEVEL',false);
        define('CONFIG_DIR',SERVER_ROOT_DIR.'/config/');

        self::$config = Spyc::YAMLLoad(CONFIG_DIR.'config.yaml');
        if (DEVEL && file_exists(CONFIG_DIR.'debug.yaml')) {
            self::$debug = Spyc::YAMLLoad(CONFIG_DIR.'debug.yaml');
        }

        if (!empty($_REQUEST['client_ip'])) {
            $_SERVER['REMOTE_ADDR']     = filter_var($_REQUEST['client_ip'],FILTER_VALIDATE_IP,FILTER_FLAG_IPV4);
            $_SERVER['HTTP_USER_AGENT'] = (!empty($_REQUEST['client_agent']))   ? filter_var($_REQUEST['client_agent'],FILTER_SANITIZE_STRING) : ((!empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0');
            $_SERVER['HTTP_USER_HOST']  = (!empty($_REQUEST['client_request'])) ? filter_var($_REQUEST['client_request'],FILTER_SANITIZE_URL)  : ((!empty($_SERVER['HTTP_USER_HOST'])) ? $_SERVER['HTTP_USER_HOST'] : '127.0.0.1');
        }

        define('GOD',(in_array($_SERVER['REMOTE_ADDR'],self::$config['gods']) || (!empty($_GET['debug']) && in_array($_GET['debug'],['err','sql']))) ? true : false);
        define('D',(in_array($_SERVER['REMOTE_ADDR'],self::$config['developers'])) ? true : false);
        define('F',(in_array($_SERVER['REMOTE_ADDR'],self::$config['my'])) ? true : false);
        define('DEBUG_ERR',(GOD && !empty($_GET['debug']) && $_GET['debug'] === 'err') ? true : false);
        define('DEBUG_SQL',(GOD && !empty($_GET['debug']) && $_GET['debug'] === 'sql') ? true : false);
        define('_____LOG',(GOD) ? true : true);
        defined('PATH_SEPARATOR') || define('PATH_SEPARATOR',(getenv('COMSPEC')) ? ';' : ':');

        $hostname = strtolower(substr($_SERVER['HTTP_HOST'],0,((($pos = strpos($_SERVER['HTTP_HOST'],':')) === false) ? strlen($_SERVER['HTTP_HOST']) : $pos)));
        define('HOSTNAME',rtrim($hostname,'.'));
        define('DEVEL_PREFIX',(preg_match('/^devel\d+/',HOSTNAME,$matches) ? $matches[0].'.' : ''));

        ini_set('memory_limit','3072M');
        ini_set('allow_call_time_pass_reference',true);
        ini_set('max_execution_time',0);
        ini_set('include_path','.:'.SERVER_ROOT_DIR.PATH_SEPARATOR.
                                    SERVER_ROOT_DIR.'/include'.PATH_SEPARATOR);
        ini_set('session.gc_maxlifetime',86400);
        ini_set('display_errors',DEBUG_ERR);
        ini_set('display_startup_errors',DEBUG_ERR);
        if (DEBUG_ERR) ini_set('error_reporting',E_ALL & ~E_NOTICE);

        bcscale(2);
        date_default_timezone_set('Europe/Berlin');

        define('DATA_DIR',SERVER_ROOT_DIR.'/data/');
        define('WORK_DIR',SERVER_ROOT_DIR.((DEVEL && is_dir(SERVER_ROOT_DIR.'/../LOGS/ancillaryapi.militaryfares.com/work/')) ? '/../LOGS/ancillaryapi.militaryfares.com/work/' : '/work/'));
        define('CACHE_DIR',WORK_DIR.'cache/');
        define('LOG_DIR',WORK_DIR.'log/');
        define('ALL_ERROR_LOG_DEST',LOG_DIR.'error/all_errors.log');
        define('ADODB_ERROR_LOG_DEST',LOG_DIR.'error/adodb_errors.log');
        define('ADODB_ERROR_LOG_TYPE',3);
        define('ENCRIPTION_KEY','I7$z.-!"rn*1U4qo');
        define('ENCRIPTION_CONTROL_STRING','DECODED_OK-');

        self::$config['ADODB_CACHE_DIR']        = CACHE_DIR.'db';
        self::$config['ADODB_FORCE_TYPE']       = ADODB_FORCE_NULL;
        self::$config['ADODB_QUOTE_FIELDNAMES'] = 'UPPER';

//        self::$config['cache_options']['cacheDir'] = str_replace('CACHE_DIR',CACHE_DIR,self::$config['cache_options']['cacheDir']);
//        self::$config['cache_options']['hashedDirectoryUmask'] = 0777;

        self::$config['session_id']     = (!empty($_REQUEST['session']) && self::is_uuid_v4($_REQUEST['session'])) ? $_REQUEST['session'] : uuid_v4();
        self::$config['log_id']         = (!empty($_REQUEST['log_id'])  && self::is_uuid_v4($_REQUEST['log_id']))  ? $_REQUEST['log_id']  : self::$config['session_id'];
        self::$config['ipaddress']      = $_SERVER['SERVER_ADDR'];
        self::$config['client_ip']      = $_SERVER['REMOTE_ADDR'];
        self::$config['client_agent']   = (!empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0';
        self::$config['client_request'] = (!empty($_SERVER['HTTP_USER_HOST']))  ? $_SERVER['HTTP_USER_HOST']  : HOSTNAME;
        self::$config['client_info']    = ['host' => gethostbyaddr(self::$config['client_ip'])];

        foreach (self::$config['db_creds'] as $key => $value) {
            OnDemandDb::register($key,$value);
        }

        $suppliers = Util::get_suppliers();
        if ($suppliers) {
            self::$config = array_replace_recursive(self::$config,$suppliers);
        }
        if (DEVEL) {
            self::$config = [...self::$config,...self::$debug];
        }
    }

    public static function get($property)
    {
        if (empty(self::$config)) {
            self::set();
        }
        return self::$config[$property];
    }

    public static function put($value)
    {
        self::$config = array_replace_recursive(self::$config,$value);
    }

    public static function view()
    {
        if (empty(self::$config)) {
            self::set();
        }
        _d(self::$config);
    }

    private static function is_uuid_v4($s)
    {
        return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i',$s);
    }
}
