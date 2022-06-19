<?php

namespace ItIsAllMail\Action;

use ItIsAllMail\Factory\PosterDriverFactory;
use ItIsAllMail\PostingQueue;
use ItIsAllMail\Factory\AddressMapperFactory;
use ItIsAllMail\Config\FetcherSourceConfig;
use ItIsAllMail\Factory\FetcherDriverFactory;
use ItIsAllMail\Config\PosterConfig;
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
            $queue->add($rawMessage);
        } else {
            $transferFilename = tempnam(sys_get_temp_dir(), "iam-post-");
            file_put_contents($transferFilename, $rawMessage);

            $mapper = (new AddressMapperFactory($this->appConfig))->findMapper($msg);
            $source = $mapper->mapMessageToSource($msg);

            $execString = "";

            $proxyCommand = $this->getProxyCommand($msg, $source);
            $execString .= !empty($proxyCommand) ? ($proxyCommand . " ") : "";

            $execString .= "php \""  . __DIR__ . DIRECTORY_SEPARATOR
                . ".." . DIRECTORY_SEPARATOR
                . ".." . DIRECTORY_SEPARATOR
                . ".." . DIRECTORY_SEPARATOR
                . "scripts" . DIRECTORY_SEPARATOR . "poster.php\""
                . " -m \"" . $transferFilename . "\"";

            Debug::debug("Starting command:\n" . $execString);

            print_r($execString);exit(1);
            system($execString, $result);
            unlink($transferFilename);
        }

        return $result;
    }


    protected function getProxyCommand(array $msg, array $source) : ?string {

        $proxyCommand = null;

        $postingDriver = (new PosterDriverFactory($this->appConfig))->findPoster($msg);
        $posterConfig = new PosterConfig($this->appConfig, $source, $postingDriver);

        try {
            $proxyCommand = $posterConfig->getOpt("poster_proxy");
        }
        catch (\Exception $e) {}

        return $proxyCommand;
    }
}
