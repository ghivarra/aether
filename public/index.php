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

/** 
 * Checking if PHP Version is compatible or need higher version
 * @var string $minPhpVersion
**/
$minPhpVersion = '8.1';

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

// load path config constant file
require __DIR__ . '/../config/Path.php';


