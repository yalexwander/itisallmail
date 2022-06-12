<?php

namespace ItIsAllMail\Factory;

use ItIsAllMail\Interfaces\PosterDriverInterface;
use ItIsAllMail\Config\DriverConfig;

class PosterDriverFactory {

    protected $appConfig;
    
    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }
    
    public function findPoster(array $msg) : PosterDriverInterface {
        $toHeader = $msg["referenced_message"]["headers"]["to"] ?? $msg["headers"]["to"];
        $parts = explode("@", $toHeader);
        $driverCode = array_pop($parts);

        foreach ($this->appConfig["drivers"] as $driverId) {
            $driverOpts = DriverConfig::getDriverConfig($driverId);

            if (! in_array("poster", $driverOpts["features"])) {
                continue;
            }

            require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Driver" . DIRECTORY_SEPARATOR
                . $driverId . DIRECTORY_SEPARATOR . $driverOpts["poster_config"]["file"];

            $driverConfig = ! empty($driverOpts["poster_config"]) ? $driverOpts["poster_config"] : [];
            $posterDriver = new $driverOpts["poster_config"]["class"]($this->appConfig, $driverConfig);

            if ($posterDriver->canProcessMessage($msg)) {
                return $posterDriver;
            }
        }

        throw new \Exception("Poster for code $driverCode not found");
    }
    
}
