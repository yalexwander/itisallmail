<?php

$rootDir = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;

require_once($rootDir . "vendor" . DIRECTORY_SEPARATOR . "autoload.php");
require_once($rootDir . "lib" . DIRECTORY_SEPARATOR . "autoload.php");

use ItIsAllMail\DriverFactory;
use ItIsAllMail\Mailbox;

$confDir = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "conf" . DIRECTORY_SEPARATOR;

$config = yaml_parse_file($confDir . "config.yml");
$sources = yaml_parse_file($confDir . "sources.yml");

$driverFactory = new DriverFactory($config);

foreach ($sources as $source) {
    $driver = $driverFactory->getFetchDriverForSource($source);

    $posts = $driver->getPosts($source);

    $m = new Mailbox($source["mailbox"] ?? $config["mailbox"]);
    $m->mergeMessages($posts);
}
