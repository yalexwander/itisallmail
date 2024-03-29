<?php

namespace ItIsAllMail\DriverCommon;

use ItIsAllMail\Storage;
use ItIsAllMail\Interfaces\MessageStorageInterface;
use ItIsAllMail\CoreTypes\Source;
use ItIsAllMail\Mailbox;

class AbstractFetcherDriver
{
    protected array $opts;
    protected string $driverCode;
    protected MessageStorageInterface $mailbox;
    protected array $appConfig;
    protected Storage $storage;

    public function __construct(array $appConfig, array $opts)
    {
        $this->appConfig = $appConfig;
        $this->opts = $opts;
        $this->storage = new Storage();
    }

    public function matchURL(string $url): bool
    {
        $filteredCode = preg_replace('/\./', '\\.', $this->getCode());
        if (preg_match("/" . $filteredCode . "/", $url)) {
            return true;
        } else {
            return false;
        }
    }

    public function getCode(): string
    {
        return $this->driverCode;
    }

    public function getOpt(string $key): mixed
    {
        return $this->opts[$key] ?? null;
    }

    /**
     * Check if we visited this page of the thread
     */
    protected function getLastURLVisited(string $threadId): ?string
    {
        return $this->storage->get($this->driverCode, $threadId . "_last_page");
    }

    /**
     * To prevent multiple refetches
     */
    protected function setLastURLVisited(string $threadId, string $url): void
    {
        $this->storage->set($this->driverCode, $threadId . "_last_page", $url);
    }

    /**
     * To not re-download first page of discussion try to fetch root message
     * from internal storage
     */
    protected function getRootMessage(string $threadId): ?string
    {
        return $this->storage->get($this->driverCode, $threadId . "_root_msg");
    }

    /**
     * Stores root message for current thread
     */
    protected function setRootMessage(string $threadId, string $msgId): void
    {
        $this->storage->set($this->driverCode, $threadId . "_root_msg", $msgId);
    }

    /**
     * For some cases fetcher need to have access to mailbox, for example when
     * source posts change id or order on site, but logically it is the same
     * posts that already was downloaded.
     */
    public function setMailbox(MessageStorageInterface $m): void
    {
        $this->mailbox = $m;
    }

    protected function getMailbox(): MessageStorageInterface
    {
        if ($this->mailbox == null) {
            die('Mailbox is not set for current driver instance');
        }

        return $this->mailbox;
    }

    protected function messageWithGivenIdAlreadyDownloaded(string $id): bool
    {
        return $this->getMailbox()->msgExists($id);
    }

    public function getAdditionalDelayBeforeNextFetch(Source $source): int
    {
        return 0;
    }

    /**
     * Clear common files can be left after source fetching, like list of
     * pages already fetched
     */
    public function clearSourceCache(Source $source): void
    {
        $this->storage->clear($this->driverCode, $source["url"] . "_last_page");
    }

    /**
     * Called after merging mailbox with merge result
     */
    public function correctFetchStrategy(Source $source, array $mergeResult): void
    {
    }
}
