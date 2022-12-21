<?php

namespace ItIsAllMail\Config;

use ItIsAllMail\Interfaces\HierarchicConfigInterface;
use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\CoreTypes\Source;

class FetcherSourceConfig implements HierarchicConfigInterface
{
    protected array $appConfig;
    protected FetchDriverInterface $driver;
    protected Source $sourceConfig;

    public function __construct(array $appConfig, FetchDriverInterface $driver, Source $sourceConfig)
    {
        $this->appConfig = $appConfig;
        $this->driver = $driver;
        $this->sourceConfig = $sourceConfig;
    }

    /**
     * Return specified config value based on where it was set
     */
    public function getOpt(string $key)  /* : mixed */
    {
        if (isset($this->sourceConfig[$key])) {
            return $this->sourceConfig[$key];
        } elseif (!empty($this->driver->getOpt($key))) {
            return $this->driver->getOpt($key);
        } elseif (isset($this->appConfig[$key])) {
            return $this->appConfig[$key];
        } else {
            throw new \Exception($key . " option is not defined");
        }
    }
}
