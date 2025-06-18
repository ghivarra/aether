<?php

declare(strict_types = 1);

namespace Aether;

class Debugger
{
    protected string $timeStr = '{elapsed_time_debug}';
    protected string $memoryStr = '{peak_memory_debug}';
    protected static string $timeOption = 'ms';
    protected static string $memoryOption = 'mb';
    public static bool $timeDebugUsed = false;
    public static bool $memoryDebugUsed = false;

    //=============================================================================================

    protected function timeDebug(string $option = 'ms'): float|int
    {
        $timeDiff = hrtime(true) - START_TIME;
        $option   = strtolower($option);

        if ($option === 'ms') {

            return round(($timeDiff / 1000000), 2);

        } elseif ($option === 's') {

            return round(($timeDiff / 1000000000), 4);

        } else { // means nanoseconds 

            return $timeDiff;
        }
    }

    //=============================================================================================

    protected function memoryDebug(string $option = 'mb'): float|int
    {
        $memory = memory_get_peak_usage();
        $option = strtolower($option);

        if ($option === 'kb') {

            return round(($memory / 1000), 2);

        } elseif ($option === 'mb') {

            return round(($memory / 1000000), 3);

        } else { // means in bytes

            return $memory;
        }
    }

    //=============================================================================================

    public function getElapsedTime(string $option = 'ms'): string
    {
        self::$timeDebugUsed = true;
        self::$timeOption = $option;

        return $this->timeStr;
    }

    //=============================================================================================

    public function getMemoryUsage(string $option = 'mb'): string
    {
        self::$memoryDebugUsed = true;
        self::$memoryOption = $option;

        return $this->memoryStr;
    }

    //=============================================================================================

    public function parse(string $view): string
    {
        $replace   = [];
        $debugData = [];

        if (self::$timeDebugUsed)
        {
            array_push($replace, $this->timeStr);
            array_push($debugData, $this->timeDebug(self::$timeOption));
        }

        if (self::$memoryDebugUsed)
        {
            array_push($replace, $this->memoryStr);
            array_push($debugData, $this->memoryDebug(self::$memoryOption));
        }

        return empty($replace) ? $view : str_replace($replace, $debugData, $view);
    }

    //=============================================================================================
}