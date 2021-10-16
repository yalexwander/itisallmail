<?php

namespace ItIsAllMail\Interfaces;

interface PostDriverInterface
{
    /**
     * Must return true if you think your driver must handle this URL
     */
    public function matchURL(string $url): bool;

    /**
     * Must return simple code like "habr.com" or "reddit.com", expecting it
     * to be appended as domain to each email. It acutally must be a domain to
     * ovveride symfony email validation constraint.
     */
    public function getCode(): string;

    /**
     * Posting to some resource. For now required parameters are somethin like:
     * @params = [
     *   "thread" => Thread ID in format according to current driver used thread fromat
     *   "body"   => Message body
     *   "attachements" => array of attachements
     */
    public function doPost(array $params): array;
}
