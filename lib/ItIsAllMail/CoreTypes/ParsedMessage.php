<?php

namespace ItIsAllMail\CoreTypes;

use ItIsAllMail\CoreTypes\ParsedMessage;

class ParsedMessage extends \ArrayObject
{
    public function getReferencedMessage(): ParsedMessage|array|null
    {
        return $this->offsetGet("referenced_message");
    }

    public function setReferencedMessage(ParsedMessage $msg): void
    {
        $this->offsetSet("referenced_message", $msg);
    }

    public function serializeForSend(): string
    {
        $serialized = clone $this;
        return json_encode($serialized, JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
    }
}
