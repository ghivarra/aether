<?php

declare(strict_types = 1);

namespace Aether;

use Aether\Validation\ValidationDriver;
use Config\App as AppConfig;

/** 
 * Validation Class
 * 
 * @class Aether
**/

class Validation
{
    protected AppConfig $config;
    protected ValidationDriver $driver;
    protected array $rules = [];

    //===============================================================================================

    public function __construct(AppConfig|null $config = null)
    {
        $this->config = is_null($config) ? new AppConfig() : $config;
        $this->driver = new ValidationDriver($this->config);
    }

    //===============================================================================================

    public function getError(string $key): array | null
    {
        return $this->driver->getError($key);
    }

    //===============================================================================================

    public function getErrors(): array
    {
        return $this->driver->getErrors();
    }

    //===============================================================================================

    public function setRules(array $rules): Validation
    {
        $this->rules = $rules;    

        // return
        return $this;
    }

    //===============================================================================================

    public function run(array $data, array $rules = []): bool
    {
        $rules = empty($rules) ? $this->rules : $rules;

        // run
        $this->driver->execute($data, $rules);

        // if error empty
        return empty($this->getErrors());
    }

    //===============================================================================================
}