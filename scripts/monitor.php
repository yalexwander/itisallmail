<?php

namespace ItIsAllMail\Script;

require_once("includes.php");

use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Scripts\Monitor;

$appConfig = yaml_parse_file($GLOBALS["__AppConfigFile"]);
$monitor = new Monitor($appConfig);

$timeMap = [];
while (true) {
    $timeMap = $monitor->rebuildUpdateTimeMap($timeMap);

    Debug::debug("New timemap is\n");
    foreach ($timeMap as $sourceId => $mapEntry) {
        Debug::debug("     " . $sourceId . " => " . date("Y-m-d H:i:s", $mapEntry["next_update"]));
    }

    foreach ($timeMap as $sourceId => $mapEntry) {
        if (time() >= $mapEntry["next_update"]) {
            $result = $monitor->runSourceUpdate($mapEntry["source"]);
            $timeMap[$sourceId]["updated"] = $result["status"];
        }
    }

    sleep(1);
}
