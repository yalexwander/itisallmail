<?php

namespace ItIsAllMail\CoreTypes;

class ParsedMessage extends \ArrayObject {
    public function getReferencedMessage() {
        return $this->offsetGet("referenced_message");
    }

    public function setReferencedMessage(ParsedMessage $msg) {
        $this->offsetSet("referenced_message", $msg); 
    }
}
