<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\DriverCommon\AbstractAddressMapper;
use ItIsAllMail\Interfaces\AddressMapperInterface;
use ItIsAllMail\SourceManager;

class HabrAddressMapper extends AbstractAddressMapper implements AddressMapperInterface {

    public function canMapThis(array $msg, string $mapType = null) : ?bool
    {
        $uri = $msg["headers"]["x-iam-uri"] ?? $msg["referenced_message"]["headers"]["x-iam-uri"];
        if (preg_match('/habr\.com\//' ,$uri)) {
            return true;
        }

        return false;
    }

    public function mapMessageToSource(array $msg) : ?array
    {
        $uri = $msg["headers"]["x-iam-uri"] ?? $msg["referenced_message"]["headers"]["x-iam-uri"];
        $sourceManager = new SourceManager($this->config);

        if (
            ! empty($uri) and
            preg_match('/(https:\/\/habr.com\/[^#]+)/', $uri, $matches)
        ) {
            $msgUrl = $matches[0];
            $source = $sourceManager->getSourceById($msgUrl);
        } else {
            return null;
        }

        return $source;
    }

    public function isCatalogAddress(string $url) : bool {
        return false;
    }
}
