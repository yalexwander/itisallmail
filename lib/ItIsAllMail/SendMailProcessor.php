<?php

namespace ItIsAllMail;

use ItIsAllMail\Action\CatalogActionHandler;
use ItIsAllMail\Action\SourceAddActionHandler;

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
        if (preg_match('/^\/(catalog|add)/', $parsedMsg["body"])) {
            return true;
        }

        if (! empty($options["c"])) {
            return true;
        }

        return false;
    }

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
        elseif ($command === 'add') {
            $logxf=fopen("/tmp/zlog.txt","a");fputs($logxf,print_r($parsedMsg, true)  . "\n");fclose($logxf);chmod("/tmp/zlog.txt", 0666);
            $sourceAddActionHandler = new SourceAddActionHandler($this->config);
            $commandResult = $sourceAddActionHandler->process($commandArg, $parsedMsg);
        }
        else {
            exit(1);
        }

        return $commandResult;
    }

}
