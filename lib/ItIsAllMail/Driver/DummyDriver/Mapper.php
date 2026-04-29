<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\DriverCommon\AbstractAddressMapper;
use ItIsAllMail\Interfaces\AddressMapperInterface;
use ItIsAllMail\SourceManager;
use ItIsAllMail\Constants;
use ItIsAllMail\CoreTypes\ParsedMessage;
use ItIsAllMail\CoreTypes\Source;

class DummyAddressMapper extends AbstractAddressMapper implements AddressMapperInterface
{
    public function canMapThis(ParsedMessage $msg, string $mapType = null): ?bool
    {
        $uri = $msg["headers"][Constants::IAM_HEADER_URI] ?? $msg["referenced_message"]["headers"][Constants::IAM_HEADER_URI] ?? null;

        if (null === $uri) {
            throw new \Exception("No attached message to reply to");
        }

        if (preg_match('/dummy/', $uri)) {
            return true;
        }

        return false;
    }

    public function mapMessageToSource(ParsedMessage $msg): ?Source
    {
        $uri = $msg["headers"][Constants::IAM_HEADER_URI] ?? $msg["referenced_message"]["headers"][Constants::IAM_HEADER_URI];
        $sourceManager = new SourceManager($this->appConfig);

        $source = null;
        if ( ! empty($uri) ) {
            $source = $sourceManager->getSourceById($uri);
        }

        return $source;
    }

    public function isCatalogAddress(string $url): bool
    {
        return false;
    }
}
