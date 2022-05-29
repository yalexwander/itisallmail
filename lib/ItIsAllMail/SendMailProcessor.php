<?php

namespace ItIsAllMail;

use ItIsAllMail\CatalogActionHandler;

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

    public function process(string $rawMessage) : int
    {
        $parsed = $this->parseMessage($rawMessage);

        if ($this->isCommandMessage($parsed)) {
            $this->processCommand($parsed);
        }
       
        return 0;
    }


    protected function isCommandMessage(array $parsedMsg): bool {
        if (preg_match('/^\/(catalog|track|untrack)/', $parsedMsg["body"])) {
            return true;
        }

        return false;
    }

    protected function processCommand(array $parsedMsg): int {
        preg_match('/^\/([a-z_\-]+)( (.+))*/', $parsedMsg["body"], $matches);
        $command = $matches[1];
        $commandArg = empty($matches[3]) ? "" : $matches[3];

        $commandResult = 1;
        if ($command === 'catalog') {
            $catalogActionHandler = new CatalogActionHandler($this->config);
            $commandResult = $catalogActionHandler->process($commandArg, $parsedMsg);
        }
        else {
            exit(1);
        }

        return $commandResult;
    }

}
