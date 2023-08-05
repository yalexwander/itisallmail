<?php

namespace ItIsAllMail\Script;

require_once("includes.php");

use ItIsAllMail\PostingQueue;
use ItIsAllMail\Action\PostActionHandler;

$appConfig = yaml_parse_file($GLOBALS["__AppConfigFile"]);
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
