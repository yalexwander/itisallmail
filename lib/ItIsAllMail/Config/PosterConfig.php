<?php

namespace ItIsAllMail\Config;

use ItIsAllMail\Interfaces\HierarchicConfigInterface;
use ItIsAllMail\Interfaces\PosterDriverInterface;
use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\Factory\PosterDriverFactory;
use ItIsAllMail\Factory\FetcherDriverFactory;

/**
  Logic of this config is based on such priorty:

  1) Option defined for given source in sources.yml
  2) Option defined for fetcher on driver level - so you don't need to configure poster in separate way
  3) Poster driver option - in case you need one account to fetch, another one to post
  4) App option - global app option if nothing else defined
 */

class PosterConfig implements HierarchicConfigInterface {

    protected $appConfig;
    protected $fetcherConfig;
    protected $sourceConfig;
    protected $posterDriver;
    protected $fetcherDriver;

    public function __construct(array $appConfig, array $sourceConfig, PosterDriverInterface $posterDriver)
    {
        $this->appConfig = $appConfig;
        $this->sourceConfig = $sourceConfig;
        $this->posterDriver = $posterDriver;
    }

    /**
     * Return specified config value based on where it was set
     */
    public function getOpt(string $key) /* : mixed */ {
        if (isset($this->sourceConfig[$key])) {
            return $this->sourceConfig[$key];
        }

        $this->fetcherDriver = (new FetcherDriverFactory($this->appConfig))
            ->getFetchDriverForSource($this->sourceConfig);
        
        $this->fetcherConfig = new FetcherSourceConfig($this->appConfig, $this->fetcherDriver, $this->sourceConfig);

        try {
            if (!empty($this->fetcherConfig->getOpt($key))) {
                return $this->fetcherConfig->getOpt($key);
            }
        }
        catch (\Exception $e) {}

        if (!empty($this->posterDriver->getOpt($key))) {
            return $this->posterDriver->getOpt($key);
        }
        
        if (isset($this->appConfig[$key])) {
            return $this->appConfig[$key];
        }
        else {
            throw new \Exception($key . " option is not defined");
        }
    }
}
