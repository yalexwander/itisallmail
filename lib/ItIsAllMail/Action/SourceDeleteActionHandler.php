<?php

namespace ItIsAllMail\Action;

use ItIsAllMail\Interfaces\CatalogDriverInterface;
use ItIsAllMail\Utils\Config\CatalogConfig;
use ItIsAllMail\Factory\CatalogDriverFactory;
use ItIsAllMail\Factory\AddressMapperFactory;
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
        
        $source = $addressMapper->mapMessageToSource($msg);

        if ($source === null) {
            return 1;
        }

        $sourceManager = new SourceManager($this->config);
        $sourceManager->deleteSource($source);

        return 0;
    }
}
