<?php

namespace ItIsAllMail\Config;

use ItIsAllMail\Interfaces\HierarchicConfigInterface;
use ItIsAllMail\Interfaces\CatalogDriverInterface;
use ItIsAllMail\CoreTypes\Source;

class CatalogConfig implements HierarchicConfigInterface
{

    protected $appConfig;
    protected $driver;
    protected $source;

    public function __construct(array $appConfig, CatalogDriverInterface $driver, Source $source)
    {
        $this->appConfig = $appConfig;
        $this->driver = $driver;
        $this->source = $source;
    }

    /**
     * Return specified config value based on where it was set
     */
    public function getOpt(string $key) /* : mixed */
    {
        if (isset($this->source[$key])) {
            return $this->source[$key];
        } elseif (isset($this->appConfig[$key])) {
            return $this->appConfig[$key];
        } else {
            throw new \Exception($key . " option is not defined");
        }
    }
}
