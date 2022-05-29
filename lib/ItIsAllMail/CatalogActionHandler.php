<?php

namespace ItIsAllMail;

use ItIsAllMail\Interfaces\CatalogDriverInterface;
use ItIsAllMail\Mailbox;
use ItIsAllMail\Utils\Config\CatalogConfig;

class CatalogActionHandler {

    protected $config;
    protected $catalogDriverFactory;

    public function __construct($config)
    {
        $this->config = $config;

        $this->catalogDriverFactory = new CatalogDriverFactory($config);
    }

    public function process(string $arg, array $msg) {
        $driver = $this->catalogDriverFactory->getCatalogDriver($arg, [ "msg" => $msg ] );

        $catalogConfig = new CatalogConfig($this->config, $driver, [ "mailbox" => $this->config["catalog_mailbox"] ]);

        $mailbox = new Mailbox($catalogConfig);

        $posts = $driver->queryCatalog($arg, [
            "parsedMessage" => $msg
        ]);

        $mergeResult = $mailbox->mergeMessages($posts);
        
        return 0;
    }
}
