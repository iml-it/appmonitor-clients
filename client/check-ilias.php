<?php
/* ______________________________________________________________________
 * 
 * A P P M O N I T O R  ::  CLIENT - CHECK
 * ______________________________________________________________________
 * 
 * Check for a ILIAS instance.
 * LMS https://www.ilias.de
 * 
 * It checks 
 * - config file must be readably
 * - default client config must be writable
 * - datatadir must be writable
 * - datatadir/[Default-client] must be writable
 * - mysql connect of default client
 * 
 * @author: Axel Hahn
 * ----------------------------------------------------------------------
 * 2018-07-16  v0.02
 * 2018-07-17  v0.03  add port in mysql check
 */

// ----------------------------------------------------------------------
// CONFIG
// ----------------------------------------------------------------------
require_once('classes/appmonitor-client.class.php');
$oMonitor = new appmonitor();
$oMonitor->setWebsite('My ILIAS Instance');

@include 'general_include.php';
require_once 'check-ilias.settings.php';

// ----------------------------------------------------------------------
// Read ILIAS specific config items
// ----------------------------------------------------------------------


$sConfigfile = $sApproot . '/ilias.ini.php';
if (!file_exists($sConfigfile)) {
    header('HTTP/1.0 503 Service Unavailable');
    die('ERROR: Config file was not found. Set a correct $sApproot pointing to Ilias install dir.');
}

$aConfig = parse_ini_file($sConfigfile, true);

$sClientConfigFile=(
	isset($aConfig['server']['absolute_path'])
	&& isset($aConfig['clients']['path']) 
	&& isset($aConfig['clients']['default']) 
	&& isset($aConfig['clients']['inifile'])
	) ? $aConfig['server']['absolute_path'].'/'.$aConfig['clients']['path'].'/'.$aConfig['clients']['default'].'/'.$aConfig['clients']['inifile']
	: false
	;

if(!$sClientConfigFile || !file_exists($sClientConfigFile)){
    header('HTTP/1.0 503 Service Unavailable');
    die('ERROR: Cannot parse client config from ['.basename($sConfigfile).'].');	
}

$aClientConfig = parse_ini_file($sClientConfigFile, true);

$aDb=array(
  'server'   => $aClientConfig['db']['host'],
  'username' => $aClientConfig['db']['user'],
  'password' => $aClientConfig['db']['pass'],
  'database' => $aClientConfig['db']['name'],
  'port'     => $aClientConfig['db']['port'],
); 

// ----------------------------------------------------------------------
// checks
// ----------------------------------------------------------------------

$oMonitor->addCheck(
    array(
        "name" => "check config file",
        "description" => "The config file must be readable",
        "check" => array(
            "function" => "File",
            "params" => array(
                "filename" => $sConfigfile,
                "file" => true,
                "readable" => true,
            ),
        ),
    )
);
$oMonitor->addCheck(
    array(
        "name" => "check config file",
        "description" => "The config file must be writable",
        "check" => array(
            "function" => "File",
            "params" => array(
                "filename" => $sClientConfigFile,
                "file" => true,
                "writable" => true,
            ),
        ),
    )
);
$oMonitor->addCheck(
    array(
        "name" => "check dir clients - datadir",
        "description" => "The datadir must be writable",
        "check" => array(
            "function" => "File",
            "params" => array(
                "filename" => $aConfig['clients']['datadir'],
                "dir" => true,
                "writable" => true,
            ),
        ),
    )
);
$oMonitor->addCheck(
    array(
        "name" => "check dir of default client",
        "description" => "The datadir must be writable",
        "check" => array(
            "function" => "File",
            "params" => array(
                "filename" => $aConfig['clients']['datadir'].'/'.$aConfig['clients']['default'],
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
                "port"     => $aDb['port'],
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
