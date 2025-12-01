<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Allow: GET, POST, OPTIONS, PUT');

define('SERVER_ROOT_DIR',str_replace("\\",'/',realpath(__DIR__.'/..')));
include(SERVER_ROOT_DIR.'/vendor/autoload.php');
include('function.php');

Config::set();

$log_id                 = Config::get('log_id');
$ADODB_CACHE_DIR        = Config::get('ADODB_CACHE_DIR');
$ADODB_FORCE_TYPE       = Config::get('ADODB_FORCE_TYPE');
$ADODB_QUOTE_FIELDNAMES = Config::get('ADODB_QUOTE_FIELDNAMES');
