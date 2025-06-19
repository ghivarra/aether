<?php

declare(strict_types = 1);

namespace Aether\Config;

class BaseConfig
{
    protected function rewriteConfig(array $config): void
    {
        foreach ($config as $key => $value):

            if (isset($this->$key))
            {
                $this->$key = $value;
            }

        endforeach;
    }

    //==================================================================================
}