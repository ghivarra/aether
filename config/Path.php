<?php

/** 
 * Front Controller Path
 * 
 * /public/
 * 
 * @var string FCPATH
**/
define('FCPATH', realpath(__DIR__ . '/../public') . DIRECTORY_SEPARATOR);

/** 
 * Root Path
 * 
 * /
 * 
 * @var string ROOTPATH
**/
define('ROOTPATH', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);

/** 
 * App Path
 * 
 * /app
 * 
 * @var string APPPATH
**/
define('APPPATH', realpath(__DIR__ . '/../app') . DIRECTORY_SEPARATOR);

/** 
 * System / Core Path
 * 
 * /core
 * 
 * @var string SYSTEMPATH
**/
define('SYSTEMPATH', realpath(__DIR__ . '/../core') . DIRECTORY_SEPARATOR);

/** 
 * Storage Path
 * 
 * /storage
 * 
 * @var string STORAGEPATH
**/
define('STORAGEPATH', realpath(__DIR__ . '/../storage') . DIRECTORY_SEPARATOR);