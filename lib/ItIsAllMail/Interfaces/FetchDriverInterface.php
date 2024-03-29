<?php

namespace ItIsAllMail\Interfaces;

use ItIsAllMail\CoreTypes\Source;

interface FetchDriverInterface
{
    /**
     * Must return true if you think your driver must handle this URL
     */
    public function matchURL(string $url): bool;

    /**
     * Must return simple code like "habr.com" or "reddit.com", expecting it
     * to be appended as domain to each email. It actually must be a domain to
     * ovveride symfony email validation constraint. Maybe it will changed
     * later, but for now ensure it matches [a-z\-\.0-9]+ regex
     */
    public function getCode(): string;

    /**
     * Must return array of <ItIsAllMail\CoreTypes\SerializationMessage> objects. You do not need to
     * care how distinguish old and new messages in thread, just return all of
     * them normalized to <ItIsAllMail\CoreTypes\SerializationMessage>. See its construct method
     */
    public function getPosts(Source $source): array;

    /**
     * Must return driver option by given name.
     */
    public function getOpt(string $key): mixed;

    /**
     * Clear any additional data left from this source, like cache, avatars, etc
     */
    public function clearSourceCache(Source $source): void;

    /**
     * Can be used to add time before next fetch in monitor. Can be negative.
     */
    public function getAdditionalDelayBeforeNextFetch(Source $source): int;

}
