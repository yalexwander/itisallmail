<?php

namespace ItIsAllMail\Action;

use ItIsAllMail\SourceManager;

class SourceAddActionHandler {
    protected $appConfig;

    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function process(string $arg, array $msg) : int {
        $source = [
            "url" => $msg["headers"]["x-iam-uri"]
        ];

        $sourceManager = new SourceManager($this->appConfig);
        $sourceManager->addSource($source);

        return 0;
    }
}
