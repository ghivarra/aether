<?php

/** 
 * Front Controller Path
 * 
 * ~root/public/
 * 
 * @var string FCPATH
**/
define('FCPATH', realpath(__DIR__ . '/../public') . DIRECTORY_SEPARATOR);

/** 
 * Root Path
 * 
 * ~root/
 * 
 * @var string ROOTPATH
**/
define('ROOTPATH', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);

/** 
 * App Path
 * 
 * ~root/app/
 * 
 * @var string APPPATH
**/
define('APPPATH', realpath(__DIR__ . '/../app') . DIRECTORY_SEPARATOR);

/** 
 * System / Core Path
 * 
 * ~root/core/
 * 
 * @var string SYSTEMPATH
**/
define('SYSTEMPATH', realpath(__DIR__ . '/../core') . DIRECTORY_SEPARATOR);

/** 
 * Resource Path
 * 
 * ~root/resource/
 * 
 * @var string RESOURCEPATH
**/
define('RESOURCEPATH', realpath(__DIR__ . '/../resource') . DIRECTORY_SEPARATOR);

/** 
 * View Path
 * 
 * ~root/resource/views/
 * 
 * @var string RESOURCEPATH
**/
define('VIEWPATH', realpath(__DIR__ . '/../resource/views') . DIRECTORY_SEPARATOR);

/** 
 * Storage Path
 * 
 * ~root/storage/
 * 
 * @var string STORAGEPATH
**/
define('STORAGEPATH', realpath(__DIR__ . '/../storage') . DIRECTORY_SEPARATOR);