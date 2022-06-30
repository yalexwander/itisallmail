<?php

namespace ItIsAllMail;

use ItIsAllMail\Message;
use ItIsAllMail\Interfaces\HierarchicConfigInterface;
use ItIsAllMail\Utils\EmailParser;
use ItIsAllMail\Constants;

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
            $headersToUpdate[] = "Subject";
        }
        if ($sourceConfig->getOpt('update_subject_header_on_changed_messages')) {
            $headersToUpdate[] = Constants::IAM_HEADER_STATUSLINE;
        }
    }

    public function updateMessageHeaders(string $sourceMIMEFile, Message $msg): int
    {
        if (! count($this->headersToUpdate)) {
            return 0;
        }

        $oldMsg = EmailParser::parseMessage(file_get_contents($sourceMIMEFile));

        $needUpdate = false;
        foreach ($this->headersToUpdate as $header) {
            if ($oldMsg["headers"][$header] !== $msg["headers"][$header]) {
                $needUpdate = true;
            }
        }

        if ($needUpdate) {
            file_put_contents($sourceMIMEFile, $msg->toMIMEString($this->sourceConfig));
            return 1;
        }

        return 0;
    }
}
