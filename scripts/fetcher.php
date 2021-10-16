<?php

$rootDir = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;

require_once($rootDir . "vendor" . DIRECTORY_SEPARATOR . "autoload.php");
require_once($rootDir . "lib" . DIRECTORY_SEPARATOR . "autoload.php");

use ItIsAllMail\FetchDriverFactory;
use ItIsAllMail\Mailbox;

$confDir = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "conf" . DIRECTORY_SEPARATOR;

$config = yaml_parse_file($confDir . "config.yml");
$sources = yaml_parse_file($confDir . "sources.yml");

$driverFactory = new FetchDriverFactory($config);

foreach ($sources as $source) {
    $driver = $driverFactory->getFetchDriverForSource($source);

    $mailboxPath = $config["mailbox_base_dir"] . DIRECTORY_SEPARATOR .
        (($source["mailbox"] ?? $driver->getOpt("mailbox")) ?? $config["mailbox"]);
    $m = new Mailbox($mailboxPath);


    // We have 2 main fail points here:
    // 1) problems with site like connection or markup changes
    // 2) Producing emails incompatible with standards
    try {
        $posts = $driver->getPosts($source);
        $m->mergeMessages($posts);
    } catch (\Exception $e) {
        printf("Failed to process source %s with driver %s\n", $source["url"], $driver->getCode());
        printf("Details:\n%s\n", $e->__toString());
    }
}
