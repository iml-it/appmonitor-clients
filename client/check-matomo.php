<?php
/* ______________________________________________________________________
 * 
 * A P P M O N I T O R  ::  CLIENT - CHECK
 * ______________________________________________________________________
 * 
 * Check for a Matomo instance.
 * Open Analytics platform - https://matomo.org/
 * 
 * It checks 
 * - the write access to the config file
 * - connect to matomo database (which is read from config)
 * 
 * @author: Axel Hahn - https://www.axel-hahn.de/
 * ----------------------------------------------------------------------
 * 2018-06-30  v0.1
 */

// ----------------------------------------------------------------------
// CONFIG
// ----------------------------------------------------------------------
require_once('classes/appmonitor-client.class.php');
$oMonitor = new appmonitor();
$oMonitor->setWebsite('My Matomo Instance');

@include 'general_include.php';
require_once 'check-matomo.settings.php';

// ----------------------------------------------------------------------
// Read Matomo specific config items
// ----------------------------------------------------------------------

$sConfigfile = $sApproot . '/config/config.ini.php';
if (!file_exists($sConfigfile)) {
    header('HTTP/1.0 503 Service Unavailable');
    die('ERROR: Config file was not found. Set a correct $sApproot pointing to Matomo install dir.');
}
$aConfig = parse_ini_file($sConfigfile, true);


// ----------------------------------------------------------------------
// checks
// ----------------------------------------------------------------------


$oMonitor->addCheck(
    array(
        "name" => "config file",
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
        "description" => "Connect mysql server " . $aConfig['database']['host'] . " as user " . $aConfig['database']['username'] . " to scheme " . $aConfig['database']['dbname'],
        "check" => array(
            "function" => "MysqlConnect",
            "params" => array(
                "server" => $aConfig['database']['host'],
                "user" => $aConfig['database']['username'],
                "password" => $aConfig['database']['password'],
                "db" => $aConfig['database']['dbname'],
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
