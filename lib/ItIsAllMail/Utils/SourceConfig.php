<?php

namespace ItIsAllMail\Utils;

use ItIsAllMail\Interfaces\FetchDriverInterface;

class SourceConfig {

    protected $appConfig;
    protected $driver;
    protected $sourceConfig;

    public function __construct(array $appConfig, FetchDriverInterface $driver, array $sourceConfig)
    {
        $this->appConfig = $appConfig;
        $this->driver = $driver;
        $this->sourceConfig = $sourceConfig;
    }

    /**
     * Return specified config value based on where it was set
     */
    public function getOpt(string $key) {
        if (! empty($this->sourceConfig[$key])) {
            return $this->sourceConfig[$key];
        }
        elseif (! empty($this->driver->getOpt($key))) {
            return $this->driver->getOpt($key);
        }
        elseif (! empty($this->appConfig[$key])) {
            return $this->appConfig[$key];
        }
        else {
            throw new \Exception($key . " option is not defined");
        }
    }
}
