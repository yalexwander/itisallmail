<?php

require_once("includes.php");

use ItIsAllMail\Factory\FetcherDriverFactory;
use ItIsAllMail\SourceManager;
use ItIsAllMail\CoreTypes\Source;

// script adds new source to list of sources


$restIndex = null;
$opts = getopt("m:d:", [], $restIndex);
$url = array_slice($argv, $restIndex);

if (empty($url)) {
    print "Usage php scripts/add_source [[-m <mailbox>] [-d <driver-code>]] <url>\n";
    exit(1);
}

$url = $url[0];

$config = yaml_parse_file($GLOBALS["__AppConfigFile"]);
$sourceManager = new SourceManager($config);

$newSourceConfig = [
    "url" => $url
];

$driverFactory = new FetcherDriverFactory($config);
$driver = null;
if (! empty($opts["d"])) {
    $driver = $driverFactory->getFetchDriverByCode($opts["d"]);
    $newSourceConfig["driver"] = $opts["d"];
} else {
    $driver = $driverFactory->getFetchDriverForSource(new Source([ "url" => $url ]));
}

if (! empty($opts["m"])) {
    $newSourceConfig["mailbox"] = $opts["m"];
}

$sourceManager->addSource(new Source($newSourceConfig));
