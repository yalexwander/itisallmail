<?php

namespace ItIsAllMail\Script;

require_once("includes.php");

use ItIsAllMail\Utils\Debug;
use ItIsAllMail\SourceManager;
use ItIsAllMail\Utils\Config\FetcherSourceConfig;
use ItIsAllMail\FetchDriverFactory;

class Monitor {

    protected $fetchDriverFactory;
    protected $sourceManager;
    protected $config;

    public function __construct(array $config)
    {
        $this->fetchDriverFactory = new FetchDriverFactory($config);
        $this->sourceManager = new SourceManager($config);
        $this->config = $config;
    }


    function rebuildUpdateTimeMap(array $oldMap) {
        $newMap = [];
        $newSources = $this->sourceManager->getSources();

        $activeSources = [];
        foreach ($newSources as $source) {
            $driver = $this->fetchDriverFactory->getFetchDriverForSource($source);

            $joinedConfig = new FetcherSourceConfig($this->config, $driver, $source);
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

    
    function runSourceUpdate(array $source) {
        $execString = "php \""  . __DIR__ . DIRECTORY_SEPARATOR . "fetcher.php\"";

        $driver = $this->fetchDriverFactory->getFetchDriverForSource($source);
        $joinedConfig = new FetcherSourceConfig($this->config, $driver, $source);

        $proxyApp = $joinedConfig->getOpt("fetcher_proxy");
        if (! empty($proxyApp)) {
            $execString = $proxyApp . " " . $execString;
        }

        $execString .= " \"" . $source["url"] . "\"";

        system($execString);
        
        return 1;
    }

}

$config = yaml_parse_file($__AppConfigFile);
$monitor = new Monitor($config);

$timeMap = [];
while (true) {
    $timeMap = $monitor->rebuildUpdateTimeMap($timeMap);

    foreach ($timeMap as $sourceId => $mapEntry) {
        if ( time() >= $mapEntry["next_update"] ) {
            $timeMap[$sourceId]["updated"] = $monitor->runSourceUpdate($mapEntry["source"]);
        }
    }

    sleep(1);
}
