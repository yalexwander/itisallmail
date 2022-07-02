<?php

namespace ItIsAllMail;

use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Interfaces\HierarchicConfigInterface;
use ItIsAllMail\MailboxUpdater;

class Mailbox
{
    protected $sourceConfig;
    protected $path;
    protected $localMessages;
    protected $mailSubdirs;
    protected $mailboxUpdater;

    public function __construct(HierarchicConfigInterface $sourceConfig)
    {
        $this->sourceConfig = $sourceConfig;

        $this->path = $this->sourceConfig->getOpt("mailbox_base_dir") . DIRECTORY_SEPARATOR .
            $this->sourceConfig->getOpt("mailbox");

        $this->localMessages = [];

        if (! file_exists($this->path)) {
            mkdir($this->path);
            mkdir($this->path . DIRECTORY_SEPARATOR . "cur");
            mkdir($this->path . DIRECTORY_SEPARATOR . "new");
            mkdir($this->path . DIRECTORY_SEPARATOR . "tmp");
        }

        $this->mailSubdirs = [
            "new" => $this->path . DIRECTORY_SEPARATOR . "new",
            "cur" => $this->path . DIRECTORY_SEPARATOR . "cur"
        ];

        $this->loadMailbox();

        $this->mailboxUpdater = new MailboxUpdater($this->sourceConfig);
    }

    /**
     * Checks if image with such id already exists
     */
    public function msgExists(string $id): bool
    {
        return (isset($this->localMessages[$id]) ? true : false);
    }

    /**
     * Read mailbox for a list of already known IDs . It highly rely on
     * neomutt scheme with added ":" to the end of filename, agter whcih one
     * flags listed
     */
    protected function loadMailbox(): void
    {
        $localFiles = [];

        foreach ($this->mailSubdirs as $msd) {
            $localFiles = array_merge($localFiles, scandir($msd));
        }

        foreach ($localFiles as $file) {
            $msgId = $file;
            $modified = strpos($file, ":");
            if ($modified !== false) {
                $msgId = substr($file, 0, $modified);
            }

            $this->localMessages[$msgId] = 1;
        }
    }

    public function mergeMessages(array $messages): array
    {
        $mergeStats = [
            "added" => 0,
            "modified" => 0
        ];

        foreach ($messages as $msg) {
            $messageFilepath = $this->mailSubdirs["new"] . DIRECTORY_SEPARATOR . $msg->getId();

            if (! $this->msgExists($msg->getId())) {
                Debug::log("Adding " . $msg->getId() . " as " . $messageFilepath);
                $mergeStats["added"]++;
                $this->localMessages[$msg->getId()] = 1;
                file_put_contents($messageFilepath, $msg->toMIMEString($this->sourceConfig));
            } else {
                if (
                    ! empty($this->sourceConfig->getOpt("update_subject_header_on_changed_messages")) or
                    ! empty($this->sourceConfig->getOpt("update_statusline_header_on_changed_messages"))
                ) {
                    $mergeStats["modified"]++;
                    $this->mailboxUpdater->updateMessageHeaders($messageFilepath, $msg);
                }
            }
        }

        return $mergeStats;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
