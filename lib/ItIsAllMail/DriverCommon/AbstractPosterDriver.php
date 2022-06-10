<?php

namespace ItIsAllMail\DriverCommon;

use ItIsAllMail\Interfaces\PosterDriverInterface;

class AbstractPosterDriver implements PosterDriverInterface {

    public function canProcessMessage(array $msg): bool {
        return false;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function post(array $params): array
    {
        throw new \Exception("Not implemented");
    }
}
