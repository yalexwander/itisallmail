<?php

namespace ItIsAllMail\Action;

use ItIsAllMail\Factory\PosterDriverFactory;
use ItIsAllMail\PostingQueue;
use ItIsAllMail\Factory\AddressMapperFactory;
use ItIsAllMail\Config\FetcherSourceConfig;
use ItIsAllMail\Factory\FetcherDriverFactory;
use ItIsAllMail\Utils\Debug;

class PostActionHandler {

    protected $appConfig;

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function process(string $arg, string $rawMessage, array $msg) : int {
        $result = 1;

        if (! empty($this->appConfig["use_posting_queue"])) {
            $queue = new PostingQueue($this->appConfig);
            $result = $queue->add($rawMessage);
        } else {
            $transferFilename = tempnam(sys_get_temp_dir(), "iam-post-");
            file_put_contents($transferFilename, $rawMessage);

            $mapper = (new AddressMapperFactory($this->appConfig))->findMapper($msg);
            $source = $mapper->mapMessageToSource($msg);

            $execString = "";
                
            if ($source !== null) {
                $driver = (new FetcherDriverFactory($this->appConfig))->getFetchDriverForSource($source);
                $fetcherConfig = new FetcherSourceConfig($this->appConfig, $driver, $source);

                if (!empty($fetcherConfig->getOpt("poster_proxy"))) {
                    $execString .= $fetcherConfig->getOpt("poster_proxy") . " ";
                }
            }

            $execString .= "php \""  . __DIR__ . DIRECTORY_SEPARATOR
                . ".." . DIRECTORY_SEPARATOR
                . ".." . DIRECTORY_SEPARATOR
                . ".." . DIRECTORY_SEPARATOR
                . "scripts" . DIRECTORY_SEPARATOR . "poster.php\""
                . " -m \"" . $transferFilename . "\"";

            Debug::debug("Starting command:\n" . $execString);
           
            $result = system($execString);
            unlink($transferFilename);
            
        }

        return (!$result["status"]);
    }

}
