<?php

namespace ItIsAllMail\Interfaces\Hooks;

use ItIsAllMail\Interfaces\Hooks\HookType;

interface HookInterface {
    public function getPriority(): int;
    public function getEventType(): HookType;
    public function run(array &$args): void;
}
