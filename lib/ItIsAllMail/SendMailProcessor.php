<?php

namespace ItIsAllMail;

use ItIsAllMail\Action\CatalogActionHandler;
use ItIsAllMail\Action\SourceAddActionHandler;
use ItIsAllMail\Action\SourceDeleteActionHandler;

class SendMailProcessor {
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function parseMessage(string $rawMessage) : array
    {
        $mime = mailparse_msg_create();

        if (! mailparse_msg_parse($mime, $rawMessage)) {
            exit(1);
        }

        $msgStructure = mailparse_msg_get_structure($mime);

        $parsedMessage = [
            "headers"      => [],
            "attachements" => [],
            "body"         => ""
        ];

        foreach ($msgStructure as $partId) {
            $partResource = mailparse_msg_get_part($mime, $partId);
            $partContent = mailparse_msg_get_part_data($partResource);

            if (! count($parsedMessage["headers"])) {
                $parsedMessage["headers"] = $partContent["headers"];

                $parsedMessage["body"] = substr(
                    $rawMessage,
                    $partContent["starting-pos-body"],
                    $partContent["ending-pos-body"] - $partContent["starting-pos-body"]
                );
            }
        }

        mailparse_msg_free($mime);

        return $parsedMessage;
    }

    public function process(string $rawMessage, array $options) : int
    {
        $parsed = $this->parseMessage($rawMessage);

        if ($this->isCommandMessage($parsed, $options)) {
            $this->processCommand($parsed, $options);
        }
       
        return 0;
    }

    protected function isCommandMessage(array $parsedMsg, array $options): bool {
        if (preg_match('/^\/(catalog|add|delete|feed|)/', $parsedMsg["body"])) {
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
    protected function processCommand(array $parsedMsg, $options): int {
        $commandSource = $options["c"] ?? $parsedMsg["body"];

        preg_match('/^\/([a-z_\-]+)( (.+))*/', $commandSource, $matches);
        $command = $matches[1];
        $commandArg = empty($matches[3]) ? "" : $matches[3];

        $commandResult = 1;
        if ($command === 'catalog') {
            $catalogActionHandler = new CatalogActionHandler($this->config);
            $commandResult = $catalogActionHandler->process($commandArg, $parsedMsg);
        }
        elseif ($command === 'feed') {
            $feedActionHandler = new FeedActionHandler($this->config);
            $commandResult = $feedActionHandler->process($commandArg, $parsedMsg);
        }
        elseif ($command === 'add') {
            $sourceAddActionHandler = new SourceAddActionHandler($this->config);
            $commandResult = $sourceAddActionHandler->process($commandArg, $parsedMsg);
        }
        elseif ($command === 'delete') {
            $sourceDeleteActionHandler = new SourceDeleteActionHandler($this->config);
            $commandResult = $sourceDeleteActionHandler->process($commandArg, $parsedMsg);
        }
        else {
            print "Wrong command $command" . "\n";
            exit(1);
        }

        return $commandResult;
    }

}
