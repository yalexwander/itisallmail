<?php

namespace ItIsAllMail\Factory;

use ItIsAllMail\Interfaces\AddressMapperInterface;
use ItIsAllMail\Config\DriverConfig;
use ItIsAllMail\CoreTypes\ParsedMessage;

/**
 * This factory does not load all mappers, because it is almost useless for
 * workflow. We will need mapper for handling only one message, when sendmail
 * started
 */


class AddressMapperFactory
{
    protected array $appConfig;

    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function findMapper(ParsedMessage $msg): AddressMapperInterface
    {
        foreach ($this->appConfig["drivers"] as $driverId) {
            $driverOpts = DriverConfig::getDriverConfig($driverId);

            if (! in_array("mapper", $driverOpts["features"])) {
                continue;
            }

            require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Driver" . DIRECTORY_SEPARATOR
                . $driverId . DIRECTORY_SEPARATOR . $driverOpts["mapper_config"]["file"];

            $driverConfig = ! empty($driverOpts["mapper_config"]) ? $driverOpts["mapper_config"] : [];
            $mapper = new $driverOpts["mapper_config"]["class"]($driverConfig);

            if ($mapper->canMapThis($msg)) {
                return $mapper;
            }
        }

        throw new \Exception("Eligible mapper not found for address " . $msg["headers"]["to"]);
    }
}
