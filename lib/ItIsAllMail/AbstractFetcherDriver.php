<?php

namespace ItIsAllMail;

use ItIsAllMail\Utils\Storage;

class AbstractFetcherDriver
{

    protected $opts;
    protected $driverCode;

    public function __construct(array $opts)
    {
        $this->opts = $opts;
    }

    public function matchURL(string $url): bool
    {
        if (preg_match("/" . $this->getCode() . "/", $url)) {
            return true;
        } else {
            return false;
        }
    }

    public function getCode(): string
    {
        return $this->driverCode;
    }


    public function getOpt(string $key)
    {
        return $this->opts[$key] ?? null;
    }

    /**
     * Check if we visited this page of the thread
     */
    protected function getLastURLVisited(string $threadId): ?string
    {
        return Storage::get($this->driverCode, $threadId . "_last_page");
    }

    /**
     * To prevent multiple refetches
     */
    protected function setLastURLVisited(string $threadId, string $url): void
    {
        Storage::set($this->driverCode, $threadId . "_last_page", $url);
    }

    /**
     * Check if we visited this page of the thread
     */
    protected function getRootMessage(string $threadId): ?string
    {
        return Storage::get($this->driverCode, $threadId . "_root_msg");
    }

    /**
     * Stores root message for current thread
     */
    protected function setRootMessage(string $threadId, string $msgId): void
    {
        Storage::set($this->driverCode, $threadId . "_root_msg", $msgId);
    }

}
