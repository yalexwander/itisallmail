<?php

namespace ItIsAllMail\Action;

use ItIsAllMail\SourceManager;
use ItIsAllMail\Constants;
use ItIsAllMail\CoreTypes\ParsedMessage;

class SourceAddActionHandler
{
    protected $appConfig;

    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function process(string $arg, ParsedMessage $msg): int
    {
        $source = [
            "url" => $msg["headers"][Constants::IAM_HEADER_URI]
        ];

        $sourceManager = new SourceManager($this->appConfig);
        $sourceManager->addSource($source);

        return 0;
    }
}
