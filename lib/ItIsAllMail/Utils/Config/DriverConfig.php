<?php

namespace ItIsAllMail\Utils\Config;

class DriverConfig {

    public static function getDriverConfig(string $driver) : array
    {
        $configPath = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Driver" . DIRECTORY_SEPARATOR
            . $driver . DIRECTORY_SEPARATOR . "driver.cfg";

        if (! file_exists($configPath)) {
            throw new \Exception("Driver config \"$configPath\" not found");
        }

        return yaml_parse_file($configPath);
    }
}
