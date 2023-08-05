<?php

namespace ItIsAllMail;

use ItIsAllMail\CoreTypes\SerializationMessage;
use ItIsAllMail\Factory\PosterDriverFactory;
use ItIsAllMail\CoreTypes\ParsedMessage;
use ItIsAllMail\Utils\EmailParser;

class PostingQueue
{
    protected array $appConfig;
    protected string $queueDir;

    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;
        $this->queueDir = $GLOBALS['__AppMainDir'] . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . '.queue';
    }

    public function add(ParsedMessage $msg): void
    {
        $qFile = $this->queueDir . DIRECTORY_SEPARATOR . microtime(true) . '.json';
        file_put_contents($qFile, $msg->serializeForSend());
    }

    public function getNextID(): ?string
    {
        $ids = array_sort($this->getAllIDs());
        return array_shift($ids);
    }

    public function getAllIDs(): array
    {
        $ids = [];

        foreach (scandir($this->queueDir) as $file) {
            if (strstr($item, '.json') === -1) {
                continue;
            }
            $ids[] = $file;
        }

        return $ids;
    }

    public function getByID(string $id): ?ParsedMessage
    {
        $msgFile = $this->queueDir . DIRECTORY_SEPARATOR . $id;
        if (! file_exists($msgFile)) {
            return null;
        }

        return EmailParser::parseJSONMessage(file_get_contents($msgFile));
    }

    public function removeByID(string $id): void
    {
        $msgFile = $this->queueDir . DIRECTORY_SEPARATOR . $id;
        unlink($msgFile);
    }

}
