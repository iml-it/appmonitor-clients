<?php
/* ______________________________________________________________________
 * 
 * A P P M O N I T O R  ::  CLIENT - CHECK
 * ______________________________________________________________________
 * 
 * Check for a Concrete5 instance.
 * CMS https://www.concrete5.org/
 * 
 * It checks 
 * - the write access to the config file
 * - the write access to the file storage
 * - connect to mysql database (which is read from config)
 * - ssl certificate (on https request only)
 * 
 * @author: Axel Hahn - https://www.axel-hahn.de/
 * ----------------------------------------------------------------------
 * 2018-06-30  v1.0
 * 2019-05-24  v1.01  detect include or standalone mode
 */

// ----------------------------------------------------------------------
// CONFIG
// ----------------------------------------------------------------------

$bStandalone=!(class_exists('appmonitor') && isset($oMonitor));
if($bStandalone){
    require_once('classes/appmonitor-client.class.php');
    $oMonitor = new appmonitor();
    $oMonitor->setWebsite('Concrete5 Instance');

    @include 'general_include.php';
}

require_once 'check-concrete5.settings.php';

// ----------------------------------------------------------------------
// Read Concrete5 specific config items
// ----------------------------------------------------------------------


$sConfigfile = $sApproot . '/application/config/database.php';
if (!file_exists($sConfigfile)) {
    header('HTTP/1.0 503 Service Unavailable');
    die('ERROR: Config file was not found. Set a correct $sApproot pointing to C5 install dir.');
}

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
        "name" => "check file storage",
        "description" => "The file storage must be writable",
        "check" => array(
            "function" => "File",
            "params" => array(
                "filename" => $sApproot .'/application/files',
                "dir" => true,
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

if($bStandalone){
	$oMonitor->setResult();
	$oMonitor->render();
}

// ----------------------------------------------------------------------
