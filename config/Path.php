<?php

/** 
 * Front Controller Path
 * 
 * <ROOT>/public/
 * 
 * @var string FCPATH
**/
define('FCPATH', realpath(__DIR__ . '/../public') . DIRECTORY_SEPARATOR);

/** 
 * Root Path
 * 
 * <ROOT>/
 * 
 * @var string ROOTPATH
**/
define('ROOTPATH', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);

/** 
 * App Path
 * 
 * <ROOT>/app/
 * 
 * @var string APPPATH
**/
define('APPPATH', realpath(__DIR__ . '/../app') . DIRECTORY_SEPARATOR);

/** 
 * System / Core Path
 * 
 * <ROOT>/core/
 * 
 * @var string SYSTEMPATH
**/
define('SYSTEMPATH', realpath(__DIR__ . '/../core') . DIRECTORY_SEPARATOR);

/** 
 * Resource Path
 * 
 * <ROOT>/resource/
 * 
 * @var string RESOURCEPATH
**/
define('RESOURCEPATH', realpath(__DIR__ . '/../resource') . DIRECTORY_SEPARATOR);

/** 
 * View Path
 * 
 * <ROOT>/app/View/
 * 
 * @var string RESOURCEPATH
**/
define('VIEWPATH', realpath(__DIR__ . '/../app/View') . DIRECTORY_SEPARATOR);

/** 
 * Storage Path
 * 
 * <ROOT>/storage/
 * 
 * @var string STORAGEPATH
**/
define('STORAGEPATH', realpath(__DIR__ . '/../storage') . DIRECTORY_SEPARATOR);