<?php

namespace ItIsAllMail\Factory;

use ItIsAllMail\Interfaces\CatalogDriverInterface;
use ItIsAllMail\DriverCommon\AbstractFetcherDriver;
use ItIsAllMail\Config\DriverConfig;
use ItIsAllMail\DriverCommon\AbstractCatalogDriver;

class CatalogDriverFactory
{
    protected $driverList = [];
    protected $appConfig;

    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;

        foreach ($this->appConfig["drivers"] as $driverId) {
            $driverOpts = DriverConfig::getDriverConfig($driverId);

            if (! in_array("catalog", $driverOpts["features"])) {
                continue;
            }

            require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Driver" . DIRECTORY_SEPARATOR
                . $driverId . DIRECTORY_SEPARATOR . $driverOpts["catalog_config"]["file"];

            $driverConfig = ! empty($driverOpts["catalog_config"]) ? $driverOpts["catalog_config"] : [];
            $this->driverList[] = new $driverOpts["catalog_config"]["class"]($this->appConfig, $driverConfig);
        }
    }

    /**
     * Tries to find matching catalog driver or use default from global config
     */
    public function getCatalogDriver(string $query, array $opts): CatalogDriverInterface
    {
        $opts["catalog_default_driver"] = $this->appConfig["catalog_default_driver"];

        foreach ($this->driverList as $driver) {
            if ($driver->canHandleQuery($query, $opts)) {
                return $driver;
            }
        }

        throw new \Exception("Catalog driver for code query \"$query\" not found");
    }
}
