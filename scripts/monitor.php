<?php

require_once "../vendor/autoload.php";
require_once "../lib/autoload.php";

use ItIsAllMail\Utils\Debug;

$confDir = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
$config = yaml_parse_file($confDir . "config.yml");

$fetcherExecutable = __DIR__ . DIRECTORY_SEPARATOR . "fetcher.php";

while (true) {
    Debug::debug("Starting fetcher...");
    system($fetcherExecutable);
    sleep($config["update_interval"]);
}
