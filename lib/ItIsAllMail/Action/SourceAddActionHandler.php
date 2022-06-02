<?php

namespace ItIsAllMail\Action;

use ItIsAllMail\SourceManager;

class SourceAddActionHandler {
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function process(string $arg, array $msg) {
        
        $source = [
            "url" => $msg["headers"]["x-iam-uri"]
        ];

        $sourceManager = new SourceManager($this->config);
        $sourceManager->addSource($source);

        return 0;
    }
}
