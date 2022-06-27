<?php

namespace ItIsAllMail\Utils;

class Storage
{
    protected static $storageDir =  __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".."
        . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR;

    public static function get(string $driver, string $key): ?string
    {
        $driverDir = self::$storageDir . self::sanitizeFilename($driver);
        if (! file_exists($driverDir)) {
            return null;
        }

        $keyFilename = $driverDir . DIRECTORY_SEPARATOR . self::sanitizeFilename($key);
        if (! file_exists($keyFilename)) {
            return null;
        }

        return file_get_contents($keyFilename);
    }

    /**
     * Saves value to a file in the driver's cache directory
     */
    public static function set(string $driver, string $key, string $value): void
    {
        $driverDir = self::$storageDir . self::sanitizeFilename($driver);
        if (! file_exists($driverDir)) {
            mkdir($driverDir);
        }

        $keyFilename = $driverDir . DIRECTORY_SEPARATOR . self::sanitizeFilename($key);
        file_put_contents($keyFilename, $value);
    }

    /**
     * Try to keep balance betwee readability and security for possible theing
     * like nicknames, urls, emails, etc
     */
    protected static function sanitizeFilename(string $key) : string
    {
        return preg_replace(
            '/[^A-Za-z0-9@\._]+/',
            '-',
            $key
        );
    }
}
