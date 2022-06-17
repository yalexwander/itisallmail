<?php

namespace ItIsAllMail\DriverCommon;

class AbstractCatalogDriver
{
    protected $appConfig;
    protected $catalogDriverConfig;

    public function __construct(array $appConfig, array $catalogDriverConfig)
    {
        $this->appConfig = $appConfig;
        $this->catalogDriverConfig = $catalogDriverConfig;
    }
    
    public function canHandleQuery(string $query, array $opts = []) : bool
    {
        return false;
    }
}
