<?php

namespace ItIsAllMail\Interfaces;

interface PosterDriverInterface
{
    /**
     * Must return true if you think your driver must handle given message
     */
    public function canProcessMessage(array $msg): bool;

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
    public function post(array $params): array;
}
