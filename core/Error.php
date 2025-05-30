<?php namespace Aether;

use \Throwable;
use Config\App;

/** 
 * Error Class
 * 
 * @class Aether\Error
**/
class Error
{
    public function execute(Throwable $error): void
    {
        // get status code
        $statusCode = $error->getCode();

        // send http response header
        if (!empty($statusCode))
        {
            http_response_code($statusCode);
        }

        // send view
        if ($statusCode === 404) {

            $filePath = file_exists(VIEWPATH . 'Error/PageNotFoundView.php') ? VIEWPATH . 'Error/PageNotFoundView.php' : SYSTEMPATH . 'View/Error/PageNotFoundView.php';

        } else {

            $filePath = file_exists(VIEWPATH . 'Error/ErrorView.php') ? VIEWPATH . 'Error/ErrorView.php' : SYSTEMPATH . 'View/Error/ErrorView.php';
        }

        require $filePath;
        exit(1);
    }
}