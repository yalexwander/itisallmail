<?php

/**
 * !!! Seems the only correct(but slow) way to update message headers is to:
 * 1) fully parse old message and extract attachements from it
 * 2) Create completely new message with changed headers
 * 3) Attach all attachements from old message
 * 4) Rewrite the old message file with serialized data
 *
 * Here is 2 function that works more or less:
 *
 *     updateMessageHeaders:
 * fast but
 * dirty way with preserving attachements and with dirty header update, can
 * break if parser or serializer will be changed or new headers to update
 * added
 *
 *     updateMessageHeadersRebuild:
 * Loses attachements but will not broke headers in any circuimstances
 */

namespace ItIsAllMail;

use ItIsAllMail\CoreTypes\SerializationMessage;
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

    // anywhere in the system headers tried to keep lowercased. But MIME
    // serializators use capitalized names, at least for standard headers
    protected $headerMap = [
        "subject" => "Subject",
        Constants::IAM_HEADER_STATUSLINE => Constants::IAM_HEADER_STATUSLINE
    ];

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

    public function updateMessageHeadersRebuild(string $sourceMIMEFile, SerializationMessage $msg): int
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
                Debug::debug("Header mismatch for \"{$header}\":\n\"{$oldMsg["headers"][$header]}\"\nvs\n\"{$newHeader}\"");
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

    public function updateMessageHeaders(string $sourceMIMEFile, SerializationMessage $msg): int
    {
        if (! count($this->headersToUpdate)) {
            return 0;
        }

        if (! file_exists($sourceMIMEFile)) {
            Debug::log("Probably racing condition with your MUA on {$sourceMIMEFile}");
            return 0;
        }

        $oldMsgRaw = file_get_contents($sourceMIMEFile);
        $newMsgRaw = $oldMsgRaw;
        $oldMsg = EmailParser::parseMessage($oldMsgRaw);

        $needUpdate = false;
        foreach ($this->headersToUpdate as $header) {
            $newHeader = $msg->getTranslatedMIMEHeader($header, $this->sourceConfig);
            if (
                ! empty($oldMsg["headers"][$header]) and
                $oldMsg["headers"][$header] !== $newHeader
            ) {
                Debug::debug("Header mismatch for \"{$header}\":\n\"{$oldMsg["headers"][$header]}\"\nvs\n\"{$newHeader}\"");

                $headerOffsetStart = mb_strpos($newMsgRaw, "\r\n{$this->headerMap[$header]}: ") + 2;
                $headerOffsetEnd = mb_strpos($newMsgRaw, "\r\n", $headerOffsetStart + 4);

                $newMsgRaw = mb_substr($newMsgRaw, 0, $headerOffsetStart) . $this->headerMap[$header] . ": " . $newHeader . mb_substr($newMsgRaw, $headerOffsetEnd);
                $needUpdate = true;
            }
        }

        if ($needUpdate) {
            file_put_contents($sourceMIMEFile, $newMsgRaw);
            return 1;
        }

        return 0;
    }

}
