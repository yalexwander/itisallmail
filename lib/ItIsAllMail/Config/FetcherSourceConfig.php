<?php

namespace ItIsAllMail\Config;

use ItIsAllMail\Interfaces\HierarchicConfigInterface;
use ItIsAllMail\Interfaces\FetchDriverInterface;

class FetcherSourceConfig implements HierarchicConfigInterface {

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
    public function getOpt(string $key)  /* : mixed */{
        if (isset($this->sourceConfig[$key])) {
            return $this->sourceConfig[$key];
        }
        elseif (!empty($this->driver->getOpt($key))) {
            return $this->driver->getOpt($key);
        }
        elseif (isset($this->appConfig[$key])) {
            return $this->appConfig[$key];
        }
        else {
            throw new \Exception($key . " option is not defined");
        }
    }
}
