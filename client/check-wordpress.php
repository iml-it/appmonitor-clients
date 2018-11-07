<?php
/* ______________________________________________________________________
 * 
 * ALPHA-VERSION - WORK IN PROGRESS - DO NOT USE YET
 * 
 * A P P M O N I T O R  ::  CLIENT - CHECK
 * ______________________________________________________________________
 * 
 * Check for a Wordpress instance.
 * Blogsoftware https://wordpress.org/
 * 
 * It checks 
 * - the write access to the config file
 * - connect to mysql database (which is read from config)
 * - ssl certificate (on https request only)
 * 
 * @author: Axel Hahn
 * ----------------------------------------------------------------------
 * 2018-11-07  v0.1
 */

// ----------------------------------------------------------------------
// CONFIG
// ----------------------------------------------------------------------
require_once('classes/appmonitor-client.class.php');
$oMonitor = new appmonitor();
$oMonitor->setWebsite('Wordpress Instance');

@include 'general_include.php';
require_once 'check-wordpress.settings.php';

// ----------------------------------------------------------------------
// Read Concrete5 specific config items
// ----------------------------------------------------------------------


$sConfigfile = $sApproot . '/wp-config.php';
if (!file_exists($sConfigfile)) {
    header('HTTP/1.0 503 Service Unavailable');
    die('ERROR: Config file was not found. Set a correct $sApproot pointing to wordpress install dir.');
}

require($sConfigfile)
$aDb=array(
  'server'   => DB_HOST,
  'username' => DB_USER,
  'password' => DB_PASSWORD,
  'database' => DB_NAME,
  // 'port'     => ??,
); 

$aConfig = include($sConfigfile);
$sActive=$aConfig['default-connection'];

if(!isset($aConfig['connections'][$sActive])){
    header('HTTP/1.0 503 Service Unavailable');
    die('ERROR: Config file application/config/database.php was read - but database connection could not be detected from it in connections -> '.$sActive.'.');
}
// print_r($aConfig['connections'][$sActive]); die();
$aDb=$aConfig['connections'][$sActive];

// ----------------------------------------------------------------------
// checks
// ----------------------------------------------------------------------

$oMonitor->addCheck(
    array(
        "name" => "check config file",
        "description" => "The config file must be writable",
        "check" => array(
            "function" => "File",
            "params" => array(
                "filename" => $sConfigfile,
                "file" => true,
                "writable" => true,
            ),
        ),
    )
);

$oMonitor->addCheck(
    array(
        "name" => "Mysql Connect",
        "description" => "Connect mysql server " . $aDb['server'] . " as user " . $aDb['username'] . " to scheme " . $aDb['database'],
        "check" => array(
            "function" => "MysqlConnect",
            "params" => array(
                "server"   => $aDb['server'],
                "user"     => $aDb['username'],
                "password" => $aDb['password'],
                "db"       => $aDb['database'],
                // "port"     => $aDb['port'],
            ),
        ),
    )
);

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']){
    $oMonitor->addCheck(
        array(
            "name" => "Certificate check",
            "description" => "Check if SSL cert is valid and does not expire soon",
            "check" => array(
                "function" => "Cert",
            ),
        )
    );
}
// ----------------------------------------------------------------------

$oMonitor->setResult();
$oMonitor->render();

// ----------------------------------------------------------------------
