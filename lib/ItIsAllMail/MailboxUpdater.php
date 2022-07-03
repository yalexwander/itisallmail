<?php

namespace ItIsAllMail;

use ItIsAllMail\Message;
use ItIsAllMail\Interfaces\HierarchicConfigInterface;
use ItIsAllMail\Utils\EmailParser;
use ItIsAllMail\Constants;
use ItIsAllMail\Utils\Debug;

/**
 * Now it just rewrites the message with full serialization. It is slow, but more reliable.
 */

class MailboxUpdater
{
    protected $sourceConfig;
    protected $headersToUpdate;

    public function __construct(HierarchicConfigInterface $sourceConfig)
    {
        $this->sourceConfig = $sourceConfig;
        $this->headersToUpdate = [];

        if ($sourceConfig->getOpt('update_statusline_header_on_changed_messages')) {
            $this->headersToUpdate[] = "subject";
        }
        if ($sourceConfig->getOpt('update_subject_header_on_changed_messages')) {
            $this->headersToUpdate[] = Constants::IAM_HEADER_STATUSLINE;
        }
    }

    public function updateMessageHeaders(string $sourceMIMEFile, Message $msg): int
    {
        if (! count($this->headersToUpdate)) {
            return 0;
        }

        if (! file_exists($sourceMIMEFile)) {
            Debug::log("Probably racing condition with your MUA on {$sourceMIMEFile}");
            return 0;
        }
        
        $oldMsg = EmailParser::parseMessage(file_get_contents($sourceMIMEFile));

        $needUpdate = false;
        foreach ($this->headersToUpdate as $header) {
            $newHeader = $msg->getTranslatedMIMEHeader($header, $this->sourceConfig);
            if (
                ! empty($oldMsg["headers"][$header]) and
                $oldMsg["headers"][$header] !== $newHeader
            ) {
                Debug::log("Header mismatch for \"{$header}\":\n{$oldMsg["headers"][$header]}\nvs\n{$newHeader}");
                $needUpdate = true;
            }
        }

        if ($needUpdate) {
            Debug::log("Updating headers for $sourceMIMEFile ...");
            file_put_contents($sourceMIMEFile, $msg->toMIMEString($this->sourceConfig));
            return 1;
        }

        return 0;
    }
}
