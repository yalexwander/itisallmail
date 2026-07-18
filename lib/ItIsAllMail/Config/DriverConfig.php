<?php

namespace ItIsAllMail\Config;
use Symfony\Component\Yaml\Yaml;

class DriverConfig
{
    public static function getDriverConfig(string $driver): array
    {
        $configPath = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Driver" . DIRECTORY_SEPARATOR
            . $driver . DIRECTORY_SEPARATOR . "driver.cfg";

        if (! file_exists($configPath)) {
            throw new \Exception("Driver config \"$configPath\" not found");
        }

        return Yaml::parseFile($configPath);
    }
}
