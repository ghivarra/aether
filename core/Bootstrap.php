<?php

declare(strict_types = 1);

// load vendor autoload.php
require_once ROOTPATH . 'vendor/autoload.php';

// load function
require_once SYSTEMPATH . 'Function.php';

// run startup
require_once SYSTEMPATH . 'Startup.php';

// init class
$startup = new Aether\Startup();
$startup->run();