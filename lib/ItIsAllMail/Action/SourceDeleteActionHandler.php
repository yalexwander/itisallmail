<?php

namespace ItIsAllMail\Action;

use ItIsAllMail\Interfaces\CatalogDriverInterface;
use ItIsAllMail\Utils\Config\CatalogConfig;
use ItIsAllMail\CatalogDriverFactory;
use ItIsAllMail\AddressMapperFactory;
use ItIsAllMail\SourceManager;

class SourceDeleteActionHandler {

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function process(string $arg, array $msg) : int {
        $mapperFactory = new AddressMapperFactory($this->config);
        $addressMapper = $mapperFactory->findMapper($msg);
        
        $source = $addressMapper->mapThreadToSource($msg);

        if ($source === null) {
            return 1;
        }

        $sourceManager = new SourceManager($this->config);
        $sourceManager->deleteSource($source);

        return 0;
    }
}
