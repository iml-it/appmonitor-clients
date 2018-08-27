<?php
/* ______________________________________________________________________
 *
 * A P P M O N I T O R  ::  CLIENT - CHECK
 * ______________________________________________________________________
 *
 * Check for a Limesurvey instance.
 * Online surveys https://www.limesurvey.org
 *
 * It checks
 * - the write access to the config file and upload
 * - connect to database (which is read from config)
 *
 * @author: Axel Hahn
 * ----------------------------------------------------------------------
 * 2018-08-27  v0.1
 */

// ----------------------------------------------------------------------
// CONFIG
// ----------------------------------------------------------------------
require_once('classes/appmonitor-client.class.php');
$oMonitor = new appmonitor();
$oMonitor->setWebsite('Limesurvey Instance');

@include 'general_include.php';
require_once 'check-limesurvey.settings.php';

// ----------------------------------------------------------------------
// Read application specific config items
// ----------------------------------------------------------------------

$sConfigfile = $sApproot . '/application/config/config.php';
if (!file_exists($sConfigfile)) {
    header('HTTP/1.0 503 Service Unavailable');
    die('ERROR: Config file was not found. Set a correct $sApproot pointing to Limesurvey install dir.');
}

define('BASEPATH', $sApproot);
$aConfig = include($sConfigfile);
$sDb=isset($aConfig['components']['db']['connectionString']) && $aConfig['components']['db']['connectionString']
        ? $aConfig['components']['db']['connectionString']
        : false;
if(!$sDb){
    header('HTTP/1.0 503 Service Unavailable');
    die('ERROR: Database settings were not found.');
}

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
        "name" => "check upload dir",
        "description" => "The upload subdir must be writable",
        "check" => array(
            "function" => "File",
            "params" => array(
                "filename" => $sApproot .'/upload',
                "dir" => true,
                "writable" => true,
            ),
        ),
    )
);
$oMonitor->addCheck(
    array(
        "name" => "PDO Connect",
        "description" => "PDO Connect to a database",
        "check" => array(
            "function" => "PdoConnect",
            "params" => array(
                "connect" => $sDb,
                "user" => $aConfig['components']['db']['username'],
                "password" => $aConfig['components']['db']['password'],
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
