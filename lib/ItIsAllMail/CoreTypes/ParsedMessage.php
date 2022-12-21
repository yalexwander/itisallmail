<?php

namespace ItIsAllMail\CoreTypes;
use ItIsAllMail\CoreTypes\ParsedMessage;

class ParsedMessage extends \ArrayObject {
    public function getReferencedMessage(): ?ParsedMessage {
        return $this->offsetGet("referenced_message");
    }

    public function setReferencedMessage(ParsedMessage $msg): void {
        $this->offsetSet("referenced_message", $msg); 
    }
}
