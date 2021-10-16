<?php

// just check new messsages ever conf["update_interval"] seconds

require_once("includes.php");

use ItIsAllMail\Utils\Debug;

$config = yaml_parse_file($__AppConfigFile);

$fetcherExecutable = __DIR__ . DIRECTORY_SEPARATOR . "fetcher.php";

while (true) {
    Debug::debug("Starting fetcher...");
    system($fetcherExecutable);
    sleep($config["update_interval"]);
}
