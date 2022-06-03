<?php

namespace ItIsAllMail;

use ItIsAllMail\Interfaces\CatalogDriverInterface;
use ItIsAllMail\AbstractFetcherDriver;
use ItIsAllMail\Utils\Config\DriverConfig;

class CatalogDriverFactory
{
    protected $driverList = [];
    protected $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;

        // to make autoloading, based on loading last requied class, work correct
        new AbstractCatalogDriver([]);

        foreach ($this->config["drivers"] as $driverId) {
            $driverOpts = DriverConfig::getDriverConfig($driverId);

            if (! in_array("catalog", $driverOpts["features"])) {
                continue;
            }

            require_once __DIR__ . DIRECTORY_SEPARATOR . "Driver" . DIRECTORY_SEPARATOR
                . $driverId . DIRECTORY_SEPARATOR . $driverOpts["catalog_config"]["file"];

            $driverConfig = ! empty($driverOpts["catalog_config"]) ? $driverOpts["catalog_config"] : [];
            $this->driverList[] = new $driverOpts["catalog_config"]["class"]($driverConfig);
        }
    }

    /**
     * Tries to find matching catalog driver or use default from global config
     */
    public function getCatalogDriver(string $query, array $opts): CatalogDriverInterface
    {
        $opts["catalog_default_driver"] = $this->config["catalog_default_driver"];
        
        foreach ($this->driverList as $driver) {
            if ($driver->canHandleQuery($query, $opts)) {
                return $driver;
            }
        }

        throw new \Exception("Catalog driver for code query \"$query\" not found");
    }
}
