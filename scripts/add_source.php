<?php

require_once("includes.php");

use ItIsAllMail\FetchDriverFactory;

// script adds new source to list of sources


$restIndex = null;
$opts = getopt("m:d:", [], $restIndex);
$url = array_slice($argv, $restIndex);

if (empty($url)) {
    print "Usage php scripts/add_source [[-m <mailbox>] [-d <driver-code>]] <url>\n";
    exit(1);
}

$url = $url[0];

$config = yaml_parse_file($__AppConfigFile);
$driverFactory = new FetchDriverFactory($config);
$sources = yaml_parse_file($__AppSourcesFile);

$newSource = [
    "url" => $url
];

$driver = null;
if (! empty($opts["d"])) {
    $driver = $driverFactory->getFetchDriverByCode($opts["d"]);
    $newSource["driver"] = $opts["d"];
} else {
    $driver = $driverFactory->getFetchDriverForSource([ "url" => $url ]);
}

if (! empty($opts["m"])) {
    $newSource["mailbox"] = $opts["m"];
}

foreach ($sources as $source) {
    if ($source["url"] === $url) {
        throw new \Exception("Source with url $url already exists");
    }
}

array_push($sources, $newSource);
yaml_emit_file($__AppSourcesFile, $sources);
