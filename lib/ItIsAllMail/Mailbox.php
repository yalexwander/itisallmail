<?php

namespace ItIsAllMail;

use ItIsAllMail\Utils\Debug;

class Mailbox
{
    protected $path;
    protected $localMessages;
    protected $mailSubdirs;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->localMessages = [];

        if (! file_exists($path)) {
            mkdir($path);
            mkdir($path . DIRECTORY_SEPARATOR . "cur");
            mkdir($path . DIRECTORY_SEPARATOR . "new");
            mkdir($path . DIRECTORY_SEPARATOR . "tmp");
        }

        $this->mailSubdirs = [
            "new" => $this->path . DIRECTORY_SEPARATOR . "new",
            "cur" => $this->path . DIRECTORY_SEPARATOR . "cur"
        ];

        $this->loadMailbox();
    }

    /**
     * Checks if image with such id already exists
     */
    protected function msgExists(string $id): bool
    {
        return (isset($this->localMessages[$id]) ? true : false);
    }

    /**
     * Read mailbox for a list of already known IDs
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
                Debug::log("Adding " . $msg->getId());
                $mergeStats["added"]++;
                $this->localMessages[$msg->getId()] = 1;
                file_put_contents($messageFilepath, $msg->toMIMEString());
            }
        }

        return $mergeStats;
    }
}
