<?php

namespace ItIsAllMail;

use ItIsAllMail\Interfaces\StorageInterface;
use ItIsAllMail\Utils\URLProcessor;

class Storage implements StorageInterface
{
    protected string $storageDir;

    public function __construct()
    {
        $this->storageDir = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".."
            . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR;
    }

    public function get(string $driverCode, string $key): ?string
    {
        $driverDir = $this->getDriverDir($driverCode);
        $keyFilename = $this->getKeyFilename($driverDir, $key);

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
    public function set(string $driverCode, string $key, string $value): void
    {
        $driverDir = $this->getDriverDir($driverCode);
        $keyFilename = $this->getKeyFilename($driverDir, $key);

        if (! file_exists($driverDir)) {
            mkdir($driverDir);
        }

        file_put_contents($keyFilename, $value);
    }

    protected function getDriverDir(string $driverCode): string
    {
        return $this->storageDir . URLProcessor::sanitizeFilename($driverCode);
    }

    protected function getKeyFilename(string $driverDir, string $key): string
    {
        return $driverDir . DIRECTORY_SEPARATOR . URLProcessor::sanitizeFilename($key);
    }


    public function clear(string $driverCode, string $key): void
    {
        $driverDir = $this->getDriverDir($driverCode);
        $keyFilename = $this->getKeyFilename($driverDir, $key);

        unlink($keyFilename);
    }
}
