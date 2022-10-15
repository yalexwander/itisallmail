<?php

namespace ItIsAllMail\Factory;

use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\Config\DriverConfig;
use ItIsAllMail\CoreTypes\Source;

class FetcherDriverFactory
{
    protected $driverList = [];

    protected $appConfig = [];

    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;

        foreach ($this->appConfig["drivers"] as $driverId) {
            $driverOpts = DriverConfig::getDriverConfig($driverId);

            if (! in_array("fetcher", $driverOpts["features"])) {
                continue;
            }

            require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Driver" . DIRECTORY_SEPARATOR
                . $driverId . DIRECTORY_SEPARATOR . $driverOpts["fetcher_config"]["file"];

            $driverConfig = ! empty($driverOpts["fetcher_config"]) ? $driverOpts["fetcher_config"] : [];
            $this->driverList[] = new $driverOpts["fetcher_config"]["class"]($this->appConfig, $driverConfig);
        }
    }


    /**
     * Tries to find matching driver
     */
    public function getFetchDriverForSource(Source $source): FetchDriverInterface
    {
        foreach ($this->driverList as $driver) {
            if (
                (!empty($source["driver"]) and $driver->getCode() === $source["driver"]) or
                $driver->matchURL($source["url"])
            ) {
                return $driver;
            }
        }

        throw new \Exception("Driver for url \"{$source["url"]}\" not found");
    }

    /**
     * Tries to find matching driver by code
     */
    public function getFetchDriverByCode(string $code): FetchDriverInterface
    {
        foreach ($this->driverList as $driver) {
            if ($driver->getCode() === $code) {
                return $driver;
            }
        }

        throw new \Exception("Driver for code \"$code\" not found");
    }
}
