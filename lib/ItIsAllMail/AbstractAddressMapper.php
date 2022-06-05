<?php

namespace ItIsAllMail;

use ItIsAllMail\Interfaces\AddressMapperInterface;

class AbstractAddressMapper implements AddressMapperInterface {

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function mapThreadToSource(array $msg) : ?array
    {
        throw new \Exception("Not implemented");
    }
}
