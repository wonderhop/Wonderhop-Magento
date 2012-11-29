<?php
// define env
defined('APPROOT') or define('APPROOT', realpath(__DIR__ . '/../' ));
defined('LOCALCONF_TAG') or define('LOCALCONF_TAG' , 'localconf');
defined('SCRIPTCODE') or define( 'SCRIPTCODE', getenv('SCRIPTCODE') ? getenv('SCRIPTCODE') : 'noid');
(SCRIPTCODE !== 'noid' and SCRIPTCODE != '') or die("Script has no ID attached ! You must define SCRIPTCODE constant with an unique string first !\n");
require_once APPROOT.'/app/Mage.php';
// init mage app
Mage::app();
chdir( APPROOT ) or die( "unable to execute chdir\n" );




function scriptConf($path, $cast = 'string')
{
    $path = LOCALCONF_TAG.'/cli/scripts/'.SCRIPTCODE.'/'.ltrim($path,'/');
    $node = Mage::getConfig()->getNode($path);
    switch ($cast) {
        case 'string': case 's':
            return (string)$node;
        case 'int': case 'i':
            return (int)$node;
    }
    return $node;
}

function scriptLog($level, $message)
{
    $logName = 'script.'.SCRIPTCODE.'.log';
    Mage::log( $message, _getLogLevel($level) , $logName, true );
}

function scriptLogErr($message)
{
    scriptLog(Zend_Log::ERR, $message);
}

function scriptLogWarn($message)
{
    scriptLog(Zend_Log::WARN, $message);
}

function scriptLogInfo($message)
{
    scriptLog(Zend_Log::INFO, $message);
}

function _getLogLevel($level)
{
    switch(is_int($level) ? $level : strtoupper($level)) {
        case Zend_Log::EMERG   : case 0 : case 'EMERG'  :
            return Zend_Log::EMERG;
        case Zend_Log::ALERT   : case 1 : case 'ALERT'  :
            return Zend_Log::ALERT;
        case Zend_Log::CRIT    : case 2 : case 'CRIT'   :
            return Zend_Log::CRIT;
        case Zend_Log::ERR     : case 3 : case 'ERR'    :
            return Zend_Log::ERR;
        case Zend_Log::WARN    : case 4 : case 'WARN'   :
            return Zend_Log::WARN;
        case Zend_Log::NOTICE  : case 5 : case 'NOTICE' :
            return Zend_Log::NOTICE;
        case Zend_Log::INFO    : case 6 : case 'INFO'   :
            return Zend_Log::INFO;
        case Zend_Log::DEBUG   : case 7 : case 'DEBUG'  : default :
            return Zend_Log::DEBUG;
    }
}



function cli_only()
{
    if (isset($_SERVER['REQUEST_METHOD']))
        die("This script cannot be run from Browser. This is the shell script.\n");
}

function scriptArgs($argname = NULL)
{
    static $args = NULL;
    if ($args === NULL) {
        $args = array();
        if (isset($_SERVER['argv'])) {
            $current = null;
            foreach ($_SERVER['argv'] as $arg) {
                $match = array();
                if (preg_match('#^--([\w\d_-]{1,})$#', $arg, $match) || preg_match('#^-([\w\d_]{1,})$#', $arg, $match)) {
                    $current = $match[1];
                    $args[$current] = true;
                } else {
                    if ($current) {
                        $args[$current] = $arg;
                    } else if (preg_match('#^([\w\d_]{1,})$#', $arg, $match)) {
                        $args[$match[1]] = true;
                    }
                }
            }
        }
    }
    if ($argname !== NULL) {
        return isset($args[$argname]) ? $args[$argname] : NULL;
    }
    return $args;
}


