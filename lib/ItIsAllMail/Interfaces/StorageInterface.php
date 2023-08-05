<?php

/**
 * Provide a simple interface for persistent data storage in basic key-value form for each individual source.
 */

namespace ItIsAllMail\Interfaces;

interface StorageInterface
{
    public function set(string $driverCode, string $key, string $value): void;
    public function get(string $driverCode, string $key): ?string;
    public function clear(string $driverCode, string $key): void;
}
