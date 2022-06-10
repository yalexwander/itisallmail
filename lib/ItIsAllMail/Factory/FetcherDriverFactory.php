<?php

namespace ItIsAllMail\Factory;

use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\Utils\Config\DriverConfig;

class FetcherDriverFactory
{
    protected $driverList = [];

    protected $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;

        foreach ($this->config["drivers"] as $driverId) {
            $driverOpts = DriverConfig::getDriverConfig($driverId);

            if (! in_array("fetcher", $driverOpts["features"])) {
                continue;
            }

            require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Driver" . DIRECTORY_SEPARATOR
                . $driverId . DIRECTORY_SEPARATOR . $driverOpts["fetcher_config"]["file"];

            $driverConfig = ! empty($driverOpts["fetcher_config"]) ? $driverOpts["fetcher_config"] : [];
            $this->driverList[] = new $driverOpts["fetcher_config"]["class"]($driverConfig);
        }
    }


    /**
     * Tries to find matching driver
     */
    public function getFetchDriverForSource(array $source): FetchDriverInterface
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
