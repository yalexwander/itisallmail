<?php

namespace ItIsAllMail\CoreTypes;
use ItIsAllMail\Interfaces\HierarchicConfigInterface;

/**
 * Passing SerializationMessage through different parts of ItIsAllMail
 * sometimes need to pass additional data, like message source or so. Keeping
 * this data in gloabla container is tricky and breaks encapsulation. So feel
 * free to store such data here.
 */

class MessageCorrData
{
    public ?HierarchicConfigInterface $source = null;
}
