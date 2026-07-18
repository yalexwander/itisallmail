<?php

namespace ItIsAllMail\Script;

define('__AppVendorAutoload', true); require_once("includes.php");

use ItIsAllMail\PostingQueue;
use ItIsAllMail\Action\PostActionHandler;
use Symfony\Component\Yaml\Yaml;

$appConfig = Yaml::parseFile($GLOBALS["__AppConfigFile"]);
$postingQueue = new PostingQueue($appConfig);

while (true) {
    $msgId = $postingQueue->getNextID();
    if ($msgId === null) {
        sleep(5);
        continue;
    }

    $msg = $postingQueue->getByID($msgId);
    $postAction = new PostActionHandler($appConfig);
    $postAction->send($msg);
}
