<?php

namespace ItIsAllMail;

use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Interfaces\VisitedMessagesInterface;
use ItIsAllMail\Interfaces\HierarchicConfigInterface;
use ItIsAllMail\Utils\URLProcessor;

class VisitedMessages implements VisitedMessagesInterface
{
    protected array $visitedIds;
    protected HierarchicConfigInterface $source;

    public function __construct(HierarchicConfigInterface $source)
    {
        $this->visitedIds = [];
        $this->source = $source;
        $this->loadStoredIds();
    }

    public function add(string $id, bool $delayed = false): void
    {
        $this->visitedIds[$id] = true;

        if (! $delayed) {
            $this->persist();
        }
    }

    public function remove(string $id, bool $delayed = false): void
    {
        unset($this->visitedIds[$id]);

        if (! $delayed) {
            $this->persist();
        }
    }

    public function check(string $id): bool
    {
        return !empty($this->visitedIds[$id]);
    }

    protected function getFileWithIDs(): string
    {
        $filename = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".."
            . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . ".visited" . DIRECTORY_SEPARATOR .
            URLProcessor::sanitizeFilename($this->source->getOpt('url')) . ".ids";
        return $filename;
    }

    public function persist(): void
    {
        $outFile = $this->getFileWithIDs();
        file_put_contents($outFile, implode("\n", array_keys($this->visitedIds)));
        Debug::log("Persisted visited messages for source " . $this->source->getOpt('url') . " into $outFile");
    }

    protected function loadStoredIds(): void
    {
        $outFile = $this->getFileWithIDs();

        if (! file_exists($outFile)) {
            return;
        }

        $fp = fopen($outFile, "r");
        while ($line = fgets($fp) and $line !== false) {
            $this->visitedIds[rtrim($line)] = 1;
        }
        fclose($fp);
    }
}
