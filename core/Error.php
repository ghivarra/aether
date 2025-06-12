<?php 

declare(strict_types = 1);

namespace Aether;

use \Throwable;
use Config\Services;

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

        $request = Services::request();
        $type    = $request->requestType();
        $accept  = $request->header('ACCEPT');

        if ($type === 'ajax' || $accept === 'application/json')
        {
            $sendData = [
                'status'  => 'error',
                'code'    => $statusCode,
                'message' => $error->getMessage(),
            ];

            if (AETHER_ENV === 'development')
            {
                $sendData['line']  = $error->getLine();
                $sendData['file']  = $error->getFile();
                $sendData['trace'] = $error->getTrace();
            }

            // echo
            echo json_encode($sendData);
            exit(1);

        } else {

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
}