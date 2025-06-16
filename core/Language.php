<?php

declare(strict_types = 1);

namespace Aether;

class Language
{
    public static string $defaultLocale = 'en';

    //==================================================================================

    public static function getFile(string $filename, string $locale): array
    {
        $langSystem = SYSTEMPATH . "Language/{$locale}/{$filename}.php";
        $langConfig = APPPATH . "Language/{$locale}/{$filename}.php";

        if (!file_exists($langConfig) && !file_exists($langSystem))
        {
            $locale     = self::$defaultLocale;
            $langConfig = APPPATH . "Language/{$locale}/{$filename}.php";
            $langSystem = SYSTEMPATH . "Language/{$locale}/{$filename}.php";
        }

        // store
        $fromSystem = file_exists($langSystem) ? include $langSystem : [];
        $fromConfig = file_exists($langConfig) ? include $langConfig : [];

        // return merged
        return array_merge($fromSystem, $fromConfig);
    }

    //==================================================================================
}