<?php

namespace ItIsAllMail\Action;

use ItIsAllMail\SourceManager;
use ItIsAllMail\Constants;

class SourceAddActionHandler
{
    protected $appConfig;

    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function process(string $arg, array $msg): int
    {
        $source = [
            "url" => $msg["headers"][Constants::IAM_HEADER_URI]
        ];

        $sourceManager = new SourceManager($this->appConfig);
        $sourceManager->addSource($source);

        return 0;
    }
}
