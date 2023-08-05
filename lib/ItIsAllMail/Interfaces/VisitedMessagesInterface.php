<?php

/**
 * Provide an interface for storing and checking visited message ids.
 */

namespace ItIsAllMail\Interfaces;

use ItIsAllMail\CoreTypes\Source;

interface VisitedMessagesInterface {
    public function add($string $id, $delayed = false) : void;
    public function remove($string $id, $delayed = false) : void;
    public function check($string $id) : bool;
    public function persist() : void;
}
