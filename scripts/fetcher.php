<?php

require_once("includes.php");

use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Config\FetcherSourceConfig;
use ItIsAllMail\Factory\FetcherDriverFactory;
use ItIsAllMail\SourceManager;
use ItIsAllMail\Mailbox;

if (empty($argv[1])) {
    print "You must specify source url/id";
    exit(1);
}

$config = yaml_parse_file($GLOBALS["__AppConfigFile"]);

$sourceManager = new SourceManager($config);
$driverFactory = new FetcherDriverFactory($config);

$source = $sourceManager->getSourceById($argv[1]);

if ($source === null) {
    throw new \Exception("Source with url $argv[1] not found. Add it first");
}

list($socket, $socketFile) = [null, null];
if (! empty($argv[2])) {
    $socketFile = $argv[2];
    $socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
}

$driver = $driverFactory->getFetchDriverForSource($source);
$sourceConfig = new FetcherSourceConfig($config, $driver, $source);

ini_set('max_execution_time', intval($sourceConfig->getOpt('max_fetch_time')));

$mailbox = new Mailbox($sourceConfig);
$driver->setMailbox($mailbox);

Debug::debug("Processing source " . $source["url"] . " with driver " . $driver->getCode());

$result = [
    "status" => false,
    "merge" => [],
];
try {
    $posts = $driver->getPosts($source);
    $mergeResult = $mailbox->mergeMessages($posts);
    $result["merge"] = $mergeResult;
    
    if ($mergeResult["added"]) {
        Debug::log("{$mergeResult["added"]} new messages in {$mailbox->getLabel()}");
    }

    $driver->correctFetchStrategy($source, $mergeResult);

    Debug::debug("Source processing finished");
    $result["status"] = true;
} catch (\Exception $e) {
    printf("Failed to process source %s with driver %s\n", $source["url"], $driver->getCode());
    printf("Details:\n%s\n", $e->__toString());
    Debug::debug("Source processing failed");
}

if ($socket !== null) {
    $encodedResult = json_encode($result);
    socket_sendto($socket, $encodedResult, strlen($encodedResult), 0, $socketFile, 0);
}
