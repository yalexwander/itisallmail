<?php

namespace ItIsAllMail;

use ItIsAllMail\Utils\Debug;
use ItIsAllMail\CoreTypes\Source;

class VisitedMessages implements VisitedMessagesInterface
{
    protected array $visitedIds;
    protected Source $source;

    public function __construct(Source $source) {
        $this->visitedIds = [];
        $this->source = $source;
        $this->loadStoredIds();
    }

    public function add($string $id, $delayed = false) : void {
        $this->visitedIds[$id] = true;

        if (! $delayed) {
            $this->persist();
        }
    }

    public function remove($string $id, $delayed = false) : void {
        unset($this->visitedIds[$id]);

        if (! $delayed) {
            $this->persist();
        }
    }

    public function check($string $id) : bool {
        return !empty($this->$visitedIds[$id]);
    }

    public function persist() {
    }


    protected function loadStoredIds(): void {
    }
}
