<?php

namespace ItIsAllMail\Action;

use ItIsAllMail\Interfaces\CatalogDriverInterface;
use ItIsAllMail\Mailbox;
use ItIsAllMail\Config\CatalogConfig;
use ItIsAllMail\Factory\CatalogDriverFactory;
use ItIsAllMail\CoreTypes\ParsedMessage;

class CatalogActionHandler
{

    protected $appConfig;
    protected $catalogDriverFactory;

    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;

        $this->catalogDriverFactory = new CatalogDriverFactory($appConfig);
    }

    public function process(string $arg, ParsedMessage $msg): int
    {
        $driver = $this->catalogDriverFactory->getCatalogDriver($arg, [ "msg" => $msg ]);

        $catalogConfig = new CatalogConfig($this->appConfig, $driver, [
            "mailbox" => $this->appConfig["catalog_mailbox"]
        ]);

        $mailbox = new Mailbox($catalogConfig);

        $posts = $driver->queryCatalog($arg, [
            "parsedMessage" => $msg
        ]);

        $mergeResult = $mailbox->mergeMessages($posts);

        return 0;
    }
}
