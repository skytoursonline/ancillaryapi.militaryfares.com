<?php
function _d($data,$exit = true,$fl = [],$trace = false,$export = false,$source = 'html')
{
    if (GOD) {
        if (php_sapi_name() === 'cli') {
            $source = 'text';
        }
        if ($source === 'xml') {
            header("Content-type: text/xml");
            echo $data;
        } elseif ($source === 'json') {
            header("Content-type: application/json");
            echo (is_array($data)) ? json_encode($data,JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) : $data;
        } else {
            if (!empty($fl)) {
                echo $fl[0].' in line '.$fl[1];
            }
            if ($source === 'html') {
                echo '<pre>';
                if ($trace) {
                    print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
                }
            }
            if (is_array($data) || is_object($data)) {
                (!$export) ? print_r($data) : var_export($data);
            } else {
                echo $data;
            }
            if ($source === 'html') {
                echo '</pre>';
            } elseif ($source === 'text') {
                echo "\n";
            }
        }
        if ($exit) {
            exit;
        }
    }
}

function autoload_ancillary($classname)
{
    $file = $classname.'.class.php';
    $dirs = [
        'class/',
        'core/',
        'core/gordian/',
        'core/airobot/ancillaries/',
        'core/airobot/checkin/',
        'cron/gordian/',
        'ancillary/gordian/',
        'ancillary/airobot/',
    ];
    foreach ($dirs as $dir) {
        $dir = SERVER_ROOT_DIR.'/include/'.$dir;
        if (is_readable($dir.$file)) {
            include_once($dir.$file);
            return;
        }
    }
}
function autoload_trait($traitname)
{
    $file = $traitname.'.trait.php';
    $dirs = [
        'trait/',
    ];
    foreach ($dirs as $dir) {
        $dir = SERVER_ROOT_DIR.'/include/'.$dir;
        if (is_readable($dir.$file)) {
            include_once($dir.$file);
            return;
        }
    }
}
spl_autoload_register('autoload_ancillary',true,true);
spl_autoload_register('autoload_trait',true,true);

function __fatalHandler()
{
    define('E_FATAL',E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
    $error = error_get_last();
    if ($error !== null && ($error['type'] & E_FATAL)) {
        $message  = '<pre>'."\n";
        $message .= 'Error type: '.$error['type']."\n";
        $message .= 'Error message: '.$error['message']."\n";
        $message .= 'Error file: '.$error['file']."\n";
        $message .= 'Error line: '.$error['line']."\n\n";
        $message .= date(DATE_ATOM)."\n\n";
        $message .= 'Remote address: '.$_SERVER['REMOTE_ADDR']."\n";
        $message .= 'Server address: '.$_SERVER['SERVER_ADDR']."\n";
        $message .= 'Server name: '.$_SERVER['SERVER_NAME']."\n";
        $message .= 'Server request: '.$_SERVER['REQUEST_URI']."\n";
        $message .= 'Post request: '.json_encode($_POST)."\n";
//        $message .= 'Session id: '.session_id()."\n";
        if (empty($_POST)) {
            $message .= "\n".'<a href="https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'">...</a>'."\n\n";
        }
        $message .= '</pre>'."\n";
        if (!function_exists('sendmail')) {
            mail('peer@brest.by','FATAL ERROR in '.$_SERVER['HTTP_HOST'],$message);
        } else {
            sendmail($message,'peer@brest.by','FATAL ERROR in '.$_SERVER['HTTP_HOST'],'support@militaryfares.com',$_SERVER['HTTP_HOST']);
        }
        exit;
    }
}
register_shutdown_function('__fatalHandler');

function session_error_handler($errno,$errstr,$errfile,$errline)
{
    if (!(error_reporting() & $errno)) return false;
    $types = [
        E_ERROR        => 'Error',
        E_USER_ERROR   => 'User Error',
        E_USER_WARNING => 'Warning',
        E_WARNING      => 'Warning',
        E_USER_NOTICE  => 'Notice',
        E_NOTICE       => 'Notice',
    ];
    $type = $types[$errno] ?? "Other($errno)";
    if (defined('SERVER_ROOT_DIR')) {
        $errfile = str_replace(SERVER_ROOT_DIR,'',$errfile);
    }
    error_log("PHP $type(".session_id()."): $errstr in $errfile on line $errline");
    return true;
}
set_error_handler('session_error_handler');

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
function uuid_v4()
{
    return Uuid::uuid4()->__toString();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
function sendmail($message,$recipient,$subject,$from,$fromname)
{
    $mail = new PHPMailer();
    $mail->isMail();
    $mail->isHTML(true);
    $mail->CharSet = 'utf-8';
    $mail->Subject = $subject;
    $mail->Body    = $message;
    $mail->setFrom($from,$fromname);
    $mail->addCustomHeader("Bounces-To: bounce@militaryfares.com");
    $mail->addCustomHeader("Errors-To: bounce@militaryfares.com");
    if (!empty($recipient)) {
        if (is_array($recipient)) {
            foreach ($recipient as $val) {
                $mail->addAddress($val);
            }
        } else {
            $mail->addAddress($recipient);
        }
    }
    $return = $mail->send();
    $mail->clearAddresses();
    return $return;
}
