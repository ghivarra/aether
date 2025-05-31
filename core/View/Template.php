<?php 

declare(strict_types = 1);

namespace Aether\View;

use Aether\Interface\TemplateInterface;

/** 
 * Template Interface
 * 
 * @class Aether\View\Template
**/

class Template implements TemplateInterface
{
    /** 
     * Template data storage
     * 
     * @var array self::$templateData
    **/
    public static array $templateData = [];

    //==================================================================================================

    /** 
     * Insert template data
     * 
     * @param array $data
     * 
     * @return void
     * 
    **/
    public static function setTemplateData(array $data): void
    {
        if (empty($templateData))
        {
            // push into template data
            self::$templateData = $data;

        } else {

            // mutate data
            foreach ($data as $key => $value):

                self::$templateData[$key] = $value;

            endforeach;
        }
    }

    //==================================================================================================
}