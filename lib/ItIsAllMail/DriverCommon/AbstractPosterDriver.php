<?php

namespace ItIsAllMail\DriverCommon;

use ItIsAllMail\Interfaces\PosterDriverInterface;

class AbstractPosterDriver implements PosterDriverInterface {

    public function __construct(array $appConfig, array $posterConfig)
    {
        $this->appConfig = $appConfig;
        $this->posterConfig = $posterConfig;
    }

    public function canProcessMessage(array $msg): bool {
        return false;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function post(array $msg, array $source = null, array $opts = []) : array
    {
        throw new \Exception("Not implemented");
    }
}
