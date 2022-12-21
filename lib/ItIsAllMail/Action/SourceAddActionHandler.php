<?php

namespace ItIsAllMail\Action;

use ItIsAllMail\SourceManager;
use ItIsAllMail\Constants;
use ItIsAllMail\CoreTypes\ParsedMessage;
use ItIsAllMail\CoreTypes\Source;

class SourceAddActionHandler
{
    protected array $appConfig;

    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function process(string $arg, ParsedMessage $msg): int
    {
        $source = new Source([
            "url" => $msg["headers"][Constants::IAM_HEADER_URI]
        ]);

        $sourceManager = new SourceManager($this->appConfig);
        $sourceManager->addSource($source);

        return 0;
    }
}
