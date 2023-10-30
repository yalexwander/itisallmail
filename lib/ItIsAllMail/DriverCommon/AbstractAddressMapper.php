<?php

namespace ItIsAllMail\DriverCommon;

use ItIsAllMail\Interfaces\AddressMapperInterface;
use ItIsAllMail\CoreTypes\ParsedMessage;
use ItIsAllMail\CoreTypes\Source;
use ItIsAllMail\SourceManager;
use ItIsAllMail\Constants;

class AbstractAddressMapper implements AddressMapperInterface
{
    protected array $appConfig;
    protected string $driverCode;

    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;
    }

    /**
     * This one works only in case source id is same as x-iam-uri URL in message
     */
    public function mapMessageToSource(ParsedMessage $msg): ?Source
    {
        $sourceManager = new SourceManager($this->appConfig);
        $url = $msg["headers"][Constants::IAM_HEADER_URI];
        return $sourceManager->getSourceById($url);
    }

    public function canMapThis(ParsedMessage $msg, string $mapType = null): ?bool
    {
        $address = $msg['headers']['to'] ?? $msg['headers']['from'];

        if (
            ! empty($address) and
            mb_strpos($address, $this->driverCode) === (mb_strlen($address) - mb_strlen($this->driverCode)) 
        ) {
            return true;
        }

        return false;
    }
}
