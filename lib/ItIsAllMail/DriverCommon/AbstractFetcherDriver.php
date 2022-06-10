<?php

namespace ItIsAllMail\DriverCommon;

use ItIsAllMail\Utils\Storage;
use ItIsAllMail\Mailbox;

class AbstractFetcherDriver
{
    protected $opts;
    protected $driverCode;
    protected $mailbox;

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
     * To not re-download first page of discussion try to fetch root message
     * from cache
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

    /**
     * For some cases fetcher need to have access to mailbox, for example when
     * source posts change id or oder on site, but logically it is the same
     * posts that already was downloaded.
     */
    public function setMailbox(Mailbox $m)
    {
        $this->mailbox = $m;
    }

    protected function getMailbox()
    {
        if ($this->mailbox === null) {
            die('Mailbox is not set for current driver instance');
        }

        return $this->mailbox;
    }

    protected function messageWithGivenIdAlreadyDownloaded(string $id): bool
    {
        return $this->getMailbox()->msgExists($id);
    }

    /**
     * Can be used to add time before next fetch in monitor
     */
    public function getAdditionalDelayBeforeNextFetch(array $source) : int {
        return 0;
    }
}
