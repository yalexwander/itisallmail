<?php

namespace ItIsAllMail\Script;

require_once("includes.php");

use ItIsAllMail\Utils\Debug;
use ItIsAllMail\SourceManager;
use ItIsAllMail\Config\FetcherSourceConfig;
use ItIsAllMail\Factory\FetcherDriverFactory;
use ItIsAllMail\CoreTypes\Source;

class Monitor {

    protected $fetchDriverFactory;
    protected $sourceManager;
    protected $appConfig;

    public function __construct(array $appConfig)
    {
        $this->fetchDriverFactory = new FetcherDriverFactory($appConfig);
        $this->sourceManager = new SourceManager($appConfig);
        $this->appConfig = $appConfig;
    }

    function rebuildUpdateTimeMap(array $oldMap) {
        $newMap = [];
        $newSources = $this->sourceManager->getSources();

        $activeSources = [];
        foreach ($newSources as $source) {
            $driver = $this->fetchDriverFactory->getFetchDriverForSource($source);

            $joinedConfig = new FetcherSourceConfig($this->appConfig, $driver, $source);

            if (! empty($joinedConfig->getOpt("fetch_disabled"))) {
                Debug::debug("Source {$source["url"]} is disabled. Skipping");
                continue;
            }

            $sourceUpdateInterval = intval($joinedConfig->getOpt("source_update_interval"))
                + $driver->getAdditionalDelayBeforeNextFetch($source);

            $betweenSourceUpdateInterval = (count($activeSources) + 1) * intval(
                $joinedConfig->getOpt("between_source_update_interval")
            );

            $totalAwaitInterval = $sourceUpdateInterval + $betweenSourceUpdateInterval;

            $sId = $source["url"];

            // new source
            if (empty($oldMap[$sId])) {
                $totalAwaitInterval = $betweenSourceUpdateInterval;

                $newMap[$sId] = [
                    "next_update" => time() + $totalAwaitInterval,
                    "source" => $source,
                    "updated" => 0
                ];

            }
            // source already was loaded
            else {
                // update time already passed
                if ($oldMap[$sId]["next_update"] < time() ) {
                    $newMap[ $sId ] = [
                        "source" => $oldMap[$sId]["source"],
                        "next_update" => time() + $totalAwaitInterval,
                        "updated" => 0
                    ];

                    Debug::debug("Next update of {$sId} will be at " .
                                 (new \DateTime())->setTimestamp($newMap[ $sId ]["next_update"])->format("Y-m-d H:i:s")
                    );
                }
                // update is in the future, leave as is
                else {
                    $newMap[$sId] = $oldMap[$sId];
                }

            }

            $activeSources[$sId] = true;
        }

        // clean removed sources from query
        if (count($newSources) !== count($oldMap)) {
            foreach ($oldMap as $url => $mapEntry) {
                if (empty($activeSources[$url])) {
                    unset($newMap[$url]);
                }
            }
        }

        return $newMap;
    }

    
    function runSourceUpdate(Source $source) : int {
        $execString = "php \""  . __DIR__ . DIRECTORY_SEPARATOR . "fetcher.php\"";

        $driver = $this->fetchDriverFactory->getFetchDriverForSource($source);
        $joinedConfig = new FetcherSourceConfig($this->appConfig, $driver, $source);

        $proxyApp = $joinedConfig->getOpt("fetcher_proxy");
        if (! empty($proxyApp)) {
            $execString = $proxyApp . " " . $execString;
        }

        $execString .= " \"" . $source["url"] . "\"";

        Debug::debug("Starting command:\n" . $execString);

        $result = system($execString);
        
        return ! $result;
    }

}

$appConfig = yaml_parse_file($__AppConfigFile);
$monitor = new Monitor($appConfig);

$timeMap = [];
while (true) {
    $timeMap = $monitor->rebuildUpdateTimeMap($timeMap);

    Debug::debug("New timemap is\n");
    foreach ($timeMap as $sourceId => $mapEntry) {
        Debug::debug("     " . $sourceId . " => " . date("Y-m-d H:i:s", $mapEntry["next_update"]));
    }

    foreach ($timeMap as $sourceId => $mapEntry) {
        if ( time() >= $mapEntry["next_update"] ) {
            $timeMap[$sourceId]["updated"] = $monitor->runSourceUpdate($mapEntry["source"]);
        }
    }

    sleep(1);
}
