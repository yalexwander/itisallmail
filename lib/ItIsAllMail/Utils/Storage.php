<?php

namespace ItIsAllMail\Utils;

class Storage
{
    protected static string $storageDir =  __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".."
        . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR;

    public static function get(string $driverCode, string $key): ?string
    {
        $driverDir = self::getDriverdir($driverCode);
        $keyFilename = self::getKeyFilename($driverDir, $key);

        if (! file_exists($driverDir)) {
            return null;
        }

        if (! file_exists($keyFilename)) {
            return null;
        }

        return file_get_contents($keyFilename);
    }

    /**
     * Saves value to a file in the driver's cache directory
     */
    public static function set(string $driverCode, string $key, string $value): void
    {
        $driverDir = self::getDriverdir($driverCode);
        $keyFilename = self::getKeyFilename($driverDir, $key);

        if (! file_exists($driverDir)) {
            mkdir($driverDir);
        }

        file_put_contents($keyFilename, $value);
    }

    /**
     * Try to keep balance betwee readability and security for possible theing
     * like nicknames, urls, emails, etc
     */
    protected static function sanitizeFilename(string $key): string
    {
        return preg_replace(
            '/[^A-Za-z0-9@\._]+/',
            '-',
            $key
        );
    }

    protected static function getDriverdir(string $driverCode): string {
        return self::$storageDir . self::sanitizeFilename($driverCode);
    }

    protected static function getKeyFilename(string $driverDir, string $key): string {
        return $driverDir . DIRECTORY_SEPARATOR . self::sanitizeFilename($key);
    }


    public static function clear(string $driverCode, string $key): void {
        $driverDir = self::getDriverdir($driverCode);
        $keyFilename = self::getKeyFilename($driverDir, $key);

        unlink($keyFilename);
    }
}
