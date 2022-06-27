<?php

namespace ItIsAllMail\DriverCommon;

use ItIsAllMail\Interfaces\AddressMapperInterface;

class AbstractAddressMapper implements AddressMapperInterface
{

    protected $appConfig;

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function mapMessageToSource(array $msg): ?array
    {
        throw new \Exception("Not implemented");
    }

    public function canMapThis(array $msg, string $mapType = null): ?bool
    {
        throw new \Exception("Not implemented");
    }
}
