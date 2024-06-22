<?php

namespace ItIsAllMail;

use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Interfaces\HierarchicConfigInterface;
use ItIsAllMail\MailboxUpdater;
use ItIsAllMail\Interfaces\MessageStorageInterface;
use ItIsAllMail\CoreTypes\Source;
use ItIsAllMail\CoreTypes\MessageCorrData;

class Mailbox implements MessageStorageInterface
{
    protected HierarchicConfigInterface $sourceConfig;
    protected string $path;
    protected array $localMessages;
    protected array $mailSubdirs;
    protected MailboxUpdater $mailboxUpdater;

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
     * Checks if message file with such id already exists
     */
    public function msgExists(string $id): bool
    {
        return (isset($this->localMessages[$id]) ? true : false);
    }

    /**
     * Return message filename if message with such id already exist
     */
    public function getMessageFileById(string $id): ?string
    {
        if (! $this->msgExists($id)) {
            return null;
        } else {
            return $this->localMessages[$id];
        }
    }

    /**
     * Read mailbox for a list of already known IDs . It highly rely on
     * neomutt scheme with added ":" to the end of filename, after which one
     * flags listed
     *
     * RACING_CONDITION - in case you update mailbox during the fetch
     */
    protected function loadMailbox(): void
    {
        foreach ($this->mailSubdirs as $msd) {
            $localFiles = scandir($msd);

            foreach ($localFiles as $file) {
                $msgId = $file;
                $modified = strpos($file, ":");
                if ($modified !== false) {
                    $msgId = substr($file, 0, $modified);
                }

                $this->localMessages[$msgId] = $msd . DIRECTORY_SEPARATOR . $file;
            }
        }
    }

    public function mergeMessages(array $messages): array
    {
        $mergeStats = [
            "added" => 0,
            "modified" => 0
        ];

        foreach ($messages as $msg) {
            $messageFilepath = $this->getMessageFileById($msg->getId());

            $msg->getCorrData()->source = $this->sourceConfig;

            if ($messageFilepath === null) {
                $newMessageFilepath = $this->mailSubdirs["new"] . DIRECTORY_SEPARATOR . $msg->getId();
                Debug::log("Adding " . $msg->getId() . " as " . $newMessageFilepath);
                $mergeStats["added"]++;
                $this->localMessages[$msg->getId()] = 1;
                file_put_contents($newMessageFilepath, $msg->toMIMEString($this->sourceConfig));
            } else {

                if (! empty($this->sourceConfig->getOpt("revisions"))) {
                    $this->mailboxUpdater->updateRevisions($messageFilepath, $msg);
                }

                if (
                    ! empty($this->sourceConfig->getOpt("update_subject_header_on_changed_messages")) or
                    ! empty($this->sourceConfig->getOpt("update_statusline_header_on_changed_messages"))
                ) {
                    $mergeStats["modified"] += $this->mailboxUpdater->updateMessageHeaders($messageFilepath, $msg);
                }
            }
        }

        return $mergeStats;
    }

    public function getLabel(): string
    {
        return $this->path;
    }
}
