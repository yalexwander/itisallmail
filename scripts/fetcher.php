<?php

require_once("includes.php");

use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\Config\FetcherSourceConfig;
use ItIsAllMail\FetchDriverFactory;
use ItIsAllMail\Mailbox;

$config = yaml_parse_file($__AppConfigFile);
$sources = yaml_parse_file($__AppSourcesFile);

$driverFactory = new FetchDriverFactory($config);

foreach ($sources as $source) {
    $driver = $driverFactory->getFetchDriverForSource($source);
    $sourceConfig = new FetcherSourceConfig($config, $driver, $source);

    $mailbox = new Mailbox($sourceConfig);
    $driver->setMailbox($mailbox);

    Debug::debug("Processing source " . $source["url"]);

    // We have 2 main fail points here:
    // 1) problems with site like connection or markup changes
    // 2) Producing emails incompatible with standards
    try {
        $posts = $driver->getPosts($source);
        $mergeResult = $mailbox->mergeMessages($posts);

        if ($mergeResult["added"]) {
            Debug::log("{$mergeResult["added"]} new messages in {$mailbox->getPath()}");
        }
    } catch (\Exception $e) {
        printf("Failed to process source %s with driver %s\n", $source["url"], $driver->getCode());
        printf("Details:\n%s\n", $e->__toString());
    }

    sleep($config["update_interval"]);
}
