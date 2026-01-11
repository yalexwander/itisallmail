<?php

namespace ItIsAllMail\Interfaces;

use ItIsAllMail\CoreTypes\Source;
use ItIsAllMail\CoreTypes\SerializationMessage;

interface EventInterface
{
    public function getPriority(): int;
    public function getType(): string;
    public function getSource(): Source;
    public function getRelatedMessage(): SerializationMessage;

    // put eveything else there
    public function getEventSpecificPayload(): mixed;
}
