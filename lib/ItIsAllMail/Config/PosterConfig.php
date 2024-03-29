<?php

namespace ItIsAllMail\Config;

use ItIsAllMail\Interfaces\HierarchicConfigInterface;
use ItIsAllMail\Interfaces\PosterDriverInterface;
use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\Factory\PosterDriverFactory;
use ItIsAllMail\Factory\FetcherDriverFactory;
use ItIsAllMail\CoreTypes\Source;

/**
  Logic of this config is based on such priorty:

  1) Option defined for given source in sources.yml
  2) Option defined for fetcher on driver level - so you don't need to configure poster in separate way
  3) Poster driver option - in case you need one account to fetch, another one to post
  4) App option - global app option if nothing else defined
 */

class PosterConfig implements HierarchicConfigInterface
{
    protected array $appConfig;
    protected FetcherSourceConfig $fetcherConfig;
    protected Source $source;
    protected PosterDriverInterface $posterDriver;
    protected FetchDriverInterface $fetcherDriver;

    public function __construct(array $appConfig, Source $source, PosterDriverInterface $posterDriver)
    {
        $this->appConfig = $appConfig;
        $this->source = $source;
        $this->posterDriver = $posterDriver;
    }

    /**
     * Return specified config value based on where it was set
     */
    public function getOpt(string $key): mixed
    {
        if (isset($this->source[$key])) {
            return $this->source[$key];
        }

        $this->fetcherDriver = (new FetcherDriverFactory($this->appConfig))
            ->getFetchDriverForSource($this->source);

        $this->fetcherConfig = new FetcherSourceConfig($this->appConfig, $this->fetcherDriver, $this->source);

        try {
            if (!empty($this->fetcherConfig->getOpt($key))) {
                return $this->fetcherConfig->getOpt($key);
            }
        } catch (\Exception $e) {
        }

        if (!empty($this->posterDriver->getOpt($key))) {
            return $this->posterDriver->getOpt($key);
        }

        if (isset($this->appConfig[$key])) {
            return $this->appConfig[$key];
        } else {
            throw new \Exception($key . " option is not defined");
        }
    }
}
