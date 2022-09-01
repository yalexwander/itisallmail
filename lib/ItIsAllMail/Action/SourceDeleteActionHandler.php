<?php

namespace ItIsAllMail\Action;

use ItIsAllMail\Interfaces\CatalogDriverInterface;
use ItIsAllMail\Config\CatalogConfig;
use ItIsAllMail\Factory\CatalogDriverFactory;
use ItIsAllMail\Factory\AddressMapperFactory;
use ItIsAllMail\Factory\FetcherDriverFactory;
use ItIsAllMail\SourceManager;
use ItIsAllMail\CoreTypes\ParsedMessage;

class SourceDeleteActionHandler
{

    protected $appConfig;

    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function process(string $arg, ParsedMessage $msg): int
    {
        $mapperFactory = new AddressMapperFactory($this->appConfig);
        $addressMapper = $mapperFactory->findMapper($msg);

        $source = $addressMapper->mapMessageToSource($msg);

        if ($source === null) {
            return 1;
        }

        $fetcherDriver = (new FetcherDriverFactory($this->appConfig))->getFetchDriverForSource($source);
        $fetcherDriver->clearSourceCache($source);

        $sourceManager = new SourceManager($this->appConfig);
        $sourceManager->deleteSource($source);

        return 0;
    }
}
