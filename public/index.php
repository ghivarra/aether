<?php

/**
 * Aether Framework
 *
 * Created with love and proud by Ghivarra Senandika Rushdie
 *
 * @package Aether Framework
 *
 * @url https://github.com/ghivarra
 * @url https://facebook.com/bcvgr
 * @url https://twitter.com/ghivarra
 * @url https://instagram.com/ghivarra
 *
**/

// turn off all errors
error_reporting(0);
ini_set('display_errors', 'off');
ini_set('display_startup_errors', '0');

/** 
 * Checking if PHP Version is compatible or need higher version
 * @var string $minPhpVersion
**/
$minPhpVersion = '8.3';

if (version_compare(PHP_VERSION, $minPhpVersion, '<'))
{
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo "Your PHP version should be {$minPhpVersion} or higher. Current version: " . PHP_VERSION;
    exit(1);
}

/** 
 * Create constant to track time
 * @var int START_TIME
**/
define('START_TIME', hrtime(true));

/** 
 * Create constant for Framework Version
 * @var string AETHER_VERSION
**/
define('AETHER_VERSION', '0.0.1-beta');

// load path config constant file
require_once __DIR__ . '/../config/Path.php';

// load bootstrap file and away we go
require_once SYSTEMPATH . 'Bootstrap.php';