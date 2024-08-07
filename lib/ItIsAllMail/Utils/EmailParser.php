<?php

namespace ItIsAllMail\Utils;

use ItIsAllMail\Utils\Debug;
use ItIsAllMail\CoreTypes\ParsedMessage;
use ItIsAllMail\MUA\Register;

class EmailParser
{
    public static function parseMessage(string $rawMessage): ParsedMessage
    {
        $mime = mailparse_msg_create();

        if (! mailparse_msg_parse($mime, $rawMessage)) {
            Debug::debug("Failed to parse message \n" . $rawMessage);
            exit(1);
        }

        $msgStructure = mailparse_msg_get_structure($mime);

        $parsedMessage = new ParsedMessage([
            "headers"      => [],
            "attachements" => [],
            "body"         => "",
            // 95% of cases it will be command message or citation here
            "referenced_message" => null,

            // for cases of multicitation for future, must NOT include the "referenced_message"
            // "related_messages" => []
        ]);

        /**
         * Below we assume that the most complex structure of MIME file we can
         * have maximum 2 levels depth. Level 1 is main MIME container. Level
         * 2 - is everything else, like referenced email, the message body,
         * attached files. More over - we try to use referenced email ONLY for
         * extracting headers not presereved by email clients in normal way.
         */

        $complexMessage = false;
        $waitingForSubmessage = false;

        foreach ($msgStructure as $partId) {
            $partResource = mailparse_msg_get_part($mime, $partId);
            $partContent = mailparse_msg_get_part_data($partResource);

            if ($waitingForSubmessage) {
                // copying headers from inlined message
                $parsedMessage["referenced_message"] = new ParsedMessage();
                $parsedMessage["referenced_message"]["headers"] = $partContent["headers"];

                // letting to process regular attached files
                $waitingForSubmessage = false;
            }

            if (! count($parsedMessage["headers"])) {
                $parsedMessage["headers"] = $partContent["headers"];
                $currentContentType = strtolower($parsedMessage["headers"]["content-type"]);
                // assuming we parsing the root part of multipart message
                if (
                    (! empty($parsedMessage["headers"]["content-type"])) and
                    ( (false !== strstr($currentContentType, "multipart/mixed;")) or
                      (false !== strstr($currentContentType, "multipart/alternative;")) )
                ) {
                    $complexMessage = true;
                    continue;
                }
                // assuming we have simple single part message
                else {
                    $bodyLength = $partContent["ending-pos-body"] - $partContent["starting-pos-body"];

                    // for handling one line message
                    $fullMessageLength = mb_strlen($rawMessage);
                    if ($bodyLength === 0 and $fullMessageLength > $partContent["ending-pos-body"]) {
                        $bodyLength = $fullMessageLength - $partContent["starting-pos-body"];
                    }

                    $parsedMessage["body"] = substr(
                        $rawMessage,
                        $partContent["starting-pos-body"],
                        $bodyLength
                    );

                    if (
                        !empty($parsedMessage["headers"]["content-transfer-encoding"]) and
                        $parsedMessage["headers"]["content-transfer-encoding"] === "quoted-printable"
                    ) {
                        $parsedMessage["body"] = quoted_printable_decode($parsedMessage["body"]);
                    }

                    break;
                }
            }
            // assuming we now parsing some part of multipart message
            elseif ($complexMessage) {
                $currentContentType = strtolower($partContent["headers"]["content-type"]);
                // assuming any inlined text part is the message itself
                if (
                    empty($parsedMessage["body"]) and
                    (false !== strstr($currentContentType, "text/plain;"))
                ) {
                    $bodyText = substr(
                        $rawMessage,
                        $partContent["starting-pos-body"],
                        $partContent["ending-pos-body"] - $partContent["starting-pos-body"]
                    );

                    // we are not inside attached message
                    if (empty($parsedMessage["referenced_message"]["headers"])) {
                        $parsedMessage["body"] = $bodyText;
                    }
                    // we parsing attached message body then
                    else {
                        $parsedMessage["referenced_message"] = $bodyText;
                    }
                }
                // so it is attached file or another MIME message
                else {
                    if (false !== strstr($partContent["headers"]["content-type"], "message/rfc822")) {
                        $waitingForSubmessage = true;
                        continue;
                    }
                }
            }
        }

        mailparse_msg_free($mime);

        // it is hack for overcoming parser addition of \r\n to the end of body
        $bodyLength = mb_strlen($parsedMessage["body"]);
        if ($bodyLength >= 2 and mb_substr($parsedMessage["body"], $bodyLength - 2, 2) == "\r\n") {
            $parsedMessage["body"] = mb_substr($parsedMessage["body"], 0, $bodyLength - 2);
        }
        // TODO: find why these symbols injected here

        return $parsedMessage;
    }

    public static function loadReferencedMessageFromRegister(string $registerName): ParsedMessage
    {
        $registerContent = (new Register())->get("reply");

        if (empty($registerContent)) {
            return new ParsedMessage();
        } else {
            return self::parseMessage(
                (new Register())->get("reply")
            );
        }
    }

    public static function parseJSONMessage(string $jsonMessage): ParsedMessage
    {
        return new ParsedMessage(json_decode($jsonMessage, true));
    }
}
