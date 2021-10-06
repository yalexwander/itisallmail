<?php

namespace ItIsAllMail;

use ItIsAllMail\DriverInterface;

class DriverFactory
{

    protected $driverList = [];

    protected $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;

        foreach ($this->config["drivers"] as $driver) {
            require_once __DIR__ . DIRECTORY_SEPARATOR . "Driver" . DIRECTORY_SEPARATOR
                . $driver . DIRECTORY_SEPARATOR . "Fetcher.php";
            $newClasses = get_declared_classes();
            $className = end($newClasses);
            $this->driverList[] = new $className();
        }
    }


    /**
     * Tries to find matching driver
     */
    public function getFetchDriverForSource(array $source): DriverInterface
    {
        foreach ($this->driverList as $driver) {
            if (
                (!empty($source["code"]) and $driver->getCode() === $source["code"]) or
                $driver->matchURL($source["url"])
            ) {
                return $driver;
            }
        }

        throw new \Exception("Driver for url \"{$source["url"]}\" not found");
    }
}
