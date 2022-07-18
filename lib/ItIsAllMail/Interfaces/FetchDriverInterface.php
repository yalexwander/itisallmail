<?php

namespace ItIsAllMail\Interfaces;

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
     * Must return array of <ItIsAllMail\Message> objects. You do not need to
     * care how distinguish old and new messages in thread, just return all of
     * them normalized to <ItIsAllMail\Message>. See its construct method
     */
    public function getPosts(array $source): array;

    /**
     * Must return driver option by given name.
     */
    public function getOpt(string $key)  /* : mixed */;
}
