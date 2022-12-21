<?php

namespace ItIsAllMail\DriverCommon;

class AbstractCatalogDriver
{
    protected array $appConfig;
    protected array $catalogDriverConfig;
    protected string $driverCode;

    public function __construct(array $appConfig, array $catalogDriverConfig)
    {
        $this->appConfig = $appConfig;
        $this->catalogDriverConfig = $catalogDriverConfig;
    }

    public function canHandleQuery(string $query, array $opts = []): bool
    {
        return false;
    }

    public function getCode(string $catalogType = null): string
    {
        return $this->driverCode;
    }

}
