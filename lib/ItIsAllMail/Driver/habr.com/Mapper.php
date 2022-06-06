<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\AbstractAddressMapper;
use ItIsAllMail\Interfaces\AddressMapperInterface;
use ItIsAllMail\SourceManager;

class HabrAddressMapper extends AbstractAddressMapper implements AddressMapperInterface {

    public function canMapThis(array $msg, $mapType = null) : ?bool
    {
        if (preg_match($msg["headers"]["to"], "@habr.com")) {
            return true;
        }

        return false;
    }

    public function mapMessageToSource(array $msg) : ?array
    {
        $msgUrl = null;

        print $msg["headers"]["x-iam-uri"] . "\n";

        if (preg_match('/(https:\/\/habr.com\/[^#]+)/', $msg["headers"]["x-iam-uri"], $matches)) {
            $msgUrl = $matches[0];
        } else {
            return null;
        }

        $sourceManager = new SourceManager($this->config);
        $source = $sourceManager->getSourceById($msgUrl);
        
        return $source;
    }

}
