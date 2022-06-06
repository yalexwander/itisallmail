<?php

namespace ItIsAllMail;

use ItIsAllMail\Interfaces\AddressMapperInterface;

class AbstractAddressMapper implements AddressMapperInterface {

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function mapMessageToSource(array $msg) : ?array
    {
        throw new \Exception("Not implemented");
    }

    public function canMapThis(array $msg) : ?bool
    {
        throw new \Exception("Not implemented");
    }

}
