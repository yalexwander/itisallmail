<?php

namespace ItIsAllMail;

use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\Interfaces\PostDriverInterface;
use ItIsAllMail\AbstractFetcherDriver;

class FetchDriverFactory
{

    protected $driverList = [];

    protected $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;

        // to make autoloading, based on loading last requied class, work correct
        new AbstractFetcherDriver([]);

        foreach ($this->config["drivers"] as $driverConf) {
            $driver = null;
            $driverOpts = [];

            if (is_array($driverConf)) {
                $driver = array_key_first($driverConf);
                $driverOpts = $driverConf[$driver];
            } else {
                $driver = $driverConf;
            }

            require_once __DIR__ . DIRECTORY_SEPARATOR . "Driver" . DIRECTORY_SEPARATOR
                . $driver . DIRECTORY_SEPARATOR . "Fetcher.php";
            $newClasses = get_declared_classes();
            $className = end($newClasses);
            $this->driverList[] = new $className($driverOpts);
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
