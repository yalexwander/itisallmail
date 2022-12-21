<?php

namespace ItIsAllMail\Action;

use ItIsAllMail\Factory\PosterDriverFactory;
use ItIsAllMail\PostingQueue;
use ItIsAllMail\Factory\AddressMapperFactory;
use ItIsAllMail\Config\FetcherSourceConfig;
use ItIsAllMail\Factory\FetcherDriverFactory;
use ItIsAllMail\Config\PosterConfig;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\CoreTypes\ParsedMessage;
use ItIsAllMail\CoreTypes\Source;

class PostActionHandler
{
    /** @var array<string, mixed> */
    protected array $appConfig;
    /** @var array<string, mixed> */
    protected array $cliOpts;

    public function __construct(array $appConfig, array $cliOpts)
    {
        $this->appConfig = $appConfig;
        $this->cliOpts = $cliOpts;
    }

    public function process(string $arg, string $rawMessage, ParsedMessage $msg): int
    {
        $result = 1;

        if (! empty($this->appConfig["use_posting_queue"])) {
            $queue = new PostingQueue($this->appConfig);
            $queue->add($msg);
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

            if (isset($this->cliOpts["r"])) {
                $execString .= " -r";
            }

            Debug::debug("Starting command:\n" . $execString);

            system($execString, $result);
            unlink($transferFilename);
        }

        return $result;
    }


    protected function getProxyCommand(ParsedMessage $msg, Source $source): ?string
    {

        $proxyCommand = null;

        $postingDriver = (new PosterDriverFactory($this->appConfig))->findPoster($msg);
        $posterConfig = new PosterConfig($this->appConfig, $source, $postingDriver);

        try {
            $proxyCommand = $posterConfig->getOpt("poster_proxy");
        } catch (\Exception $e) {
        }

        return $proxyCommand;
    }
}
