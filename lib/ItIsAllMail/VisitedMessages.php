<?php

namespace ItIsAllMail;

use ItIsAllMail\Utils\Debug;
use ItIsAllMail\CoreTypes\Source;
use ItIsAllMail\Interfaces\VisitedMessagesInterface;

class VisitedMessages implements VisitedMessagesInterface
{
    protected array $visitedIds;
    protected Source $source;

    public function __construct(Source $source)
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

    public function persist(): void
    {
    }


    protected function loadStoredIds(): void
    {
    }
}
