<?php

namespace ItIsAllMail;

use ItIsAllMail\Action\CatalogActionHandler;
use ItIsAllMail\Action\SourceAddActionHandler;
use ItIsAllMail\Action\SourceDeleteActionHandler;
use ItIsAllMail\Action\PostActionHandler;
use ItIsAllMail\Utils\EmailParser;

class SendMailProcessor
{
    protected $appConfig;

    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;
    }



    public function process(string $rawMessage, array $options): int
    {
        $parsed = EmailParser::parseMessage($rawMessage);

        if ($this->isCommandMessage($parsed, $options)) {
            return $this->processCommand($rawMessage, $parsed, $options);
        }

        return 1;
    }

    protected function isCommandMessage(array $parsedMsg, array $options): bool
    {
        if (preg_match('/^\/([a-z]+)/', $parsedMsg["body"])) {
            return true;
        }

        if (! empty($options["c"])) {
            return true;
        }

        return false;
    }

    /**
     * Adding new commands remember, that this dunction must return 0 on
     * success, because this exit code will be passed as sendmail exit code
     */
    protected function processCommand(string $rawMessage, array $parsedMsg, array $options): int
    {
        $commandSource = $options["c"] ?? $parsedMsg["body"];

        preg_match('/^\/([a-z_\-]+)( (.+))*/', $commandSource, $matches);
        $command = $matches[1];
        $commandArg = empty($matches[3]) ? "" : $matches[3];

        $commandResult = 1;
        if ($command === 'catalog') {
            $catalogActionHandler = new CatalogActionHandler($this->appConfig);
            $commandResult = $catalogActionHandler->process($commandArg, $parsedMsg);
        } elseif ($command === 'add') {
            $sourceAddActionHandler = new SourceAddActionHandler($this->appConfig);
            $commandResult = $sourceAddActionHandler->process($commandArg, $parsedMsg);
        } elseif ($command === 'delete') {
            $sourceDeleteActionHandler = new SourceDeleteActionHandler($this->appConfig);
            $commandResult = $sourceDeleteActionHandler->process($commandArg, $parsedMsg);
        } elseif ($command === 'post') {
            $postActionHandler = new PostActionHandler($this->appConfig);
            $commandResult = $postActionHandler->process($commandArg, $rawMessage, $parsedMsg);
        } else {
            print "Wrong command $command" . "\n";
            exit(1);
        }

        return $commandResult;
    }
}
