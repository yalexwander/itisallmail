<?php

namespace ItIsAllMail\DriverCommon;

use ItIsAllMail\Interfaces\AddressMapperInterface;
use ItIsAllMail\CoreTypes\ParsedMessage;

class AbstractAddressMapper implements AddressMapperInterface
{

    protected $appConfig;

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function mapMessageToSource(ParsedMessage $msg): ?array
    {
        throw new \Exception("Not implemented");
    }

    public function canMapThis(ParsedMessage $msg, string $mapType = null): ?bool
    {
        throw new \Exception("Not implemented");
    }
}
