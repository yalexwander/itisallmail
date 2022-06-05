<?php

namespace ItIsAllMail;

use ItIsAllMail\Interfaces\AddressMapperInterface;
use ItIsAllMail\Utils\Config\DriverConfig;

/**
 * This factory does not load all mappers, because it is almost useless for
 * workflow. We will need mapper for handling only one message, when sendmail
 * started
 */


class AddressMapperFactory {

    protected $config;
    
    public function __construct($config)
    {
        $this->config = $config;
    }
    
    public function findMapper(array $msg) : AddressMapperInterface {
        $parts = explode("@", $msg["headers"]["to"]);
        $driverCode = array_pop($parts);

        foreach ($this->config["drivers"] as $driverId) {
            $driverOpts = DriverConfig::getDriverConfig($driverId);

            if (! in_array("mapper", $driverOpts["features"])) {
                continue;
            }

            require_once __DIR__ . DIRECTORY_SEPARATOR . "Driver" . DIRECTORY_SEPARATOR
                . $driverId . DIRECTORY_SEPARATOR . $driverOpts["mapper_config"]["file"];

            $driverConfig = ! empty($driverOpts["mapper_config"]) ? $driverOpts["mapper_config"] : [];
            return new $driverOpts["mapper_config"]["class"]($driverConfig);
        }

        throw new \Exception("Mapper for code $driverCode not found");
    }
}