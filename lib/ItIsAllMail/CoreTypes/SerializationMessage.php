<?php

namespace ItIsAllMail\CoreTypes;

use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Interfaces\HierarchicConfigInterface;
use ItIsAllMail\Constants;
use ItIsAllMail\CoreTypes\SerializationAttachement;
use ItIsAllMail\CoreTypes\MessageCorrData;
use ItIsAllMail\CoreTypes\Source;
use ItIsAllMail\Utils\MailHeaderProcessor;

/**
 * This class represents internal message. It does not maps directly to MIME
 * or any other data structure. It is just a bridge to define features and
 * pieces of information you can found in modern messengers and how it can be
 * mapped to MIME.
 */

class SerializationMessage
{
    protected ?string $subject;
    protected ?string $from;
    protected ?string $parent;
    protected \DateTime $created;
    protected string $id;
    protected string $body;
    protected ?string $thread;
    protected ?array $attachements = [];
    protected ?array $attachementLinks = [];
    protected ?MessageCorrData $corrData;

    // this is list of extra headers, that can be useful in many places
    protected array $extraHeaders = [
        'mentions', 'score', 'reference', 'uri'
    ];

    // list of all users of given site/network/messenger mention in this message
    protected ?array $mentions;

    // count of likes and dislikes if presented
    protected ?array $score;

    // list of message IDs this message references to
    protected ?array $reference;

    // URI that specifies link of filepath, ot unique ID that can be directly
    // converted to message source
    protected ?string $uri;

    // needed for rendering purposes when we know attachements could be
    // already downloaded
    protected ?array $externalAttachements = [];

    // to store raw message data from source like json/html/etc
    protected ?string $rawSourceData;

    public function __construct(array $msgSource)
    {
        $this->subject = $msgSource["subject"];
        $this->from = $msgSource["from"];
        $this->parent = $msgSource["parent"];
        $this->created = $msgSource["created"];
        $this->id = $msgSource["id"];
        $this->body = $msgSource["body"];
        $this->thread = $msgSource["thread"];
        $this->attachements = $msgSource["attachements"] ?? [];
        $this->mentions = $msgSource["mentions"] ?? [];
        $this->uri = $msgSource["uri"] ?? null;
        $this->score = $msgSource["score"] ?? null;
        $this->rawSourceData = $msgSource["rawSourceData"] ?? null;
        $this->corrData = $msgSource["corrData"] ?? new MessageCorrData();
    }


    public function toMIMEString(HierarchicConfigInterface $sourceConfig): string
    {
        Debug::debug("Trying convert to MIME:");
        Debug::debug(Debug::dumpMessage($this));
        $envelope = [];
        $envelope["from"] = $this->from;
        $envelope["to"]  = $this->thread;
        $envelope["date"]  = $this->created->format("D, d M Y H:i:s O");
        $envelope["subject"]  = $this->getFormattedSubject($sourceConfig);
        $envelope["message_id"]  = "<" . $this->getId() . ">";

        $envelope["custom_headers"] = $this->createExtraHeaders($sourceConfig);

        if ($this->parent !== null) {
            $envelope["custom_headers"][] = "References: <" . $this->getParent() . ">";
        }

        $bodies = [];
        if ($sourceConfig->getOpt("attach_raw_message") and $this->rawSourceData !== null) {
            $this->addAttachement("iam_raw_message.txt", $this->rawSourceData);
        }

        $allAttachements = array_merge($this->attachements, $this->attachementLinks);

        if (count($allAttachements)) {
            $bodies[] = [
                "type" => TYPEMULTIPART,
                "subtype" => "alternative"
            ];
        }

        $bodyPart = [
            "type" => "text",
            "subtype" => "plain",
            "charset" => "utf-8",
            "contents.data" => $this->body
        ];

        $bodies[] = $bodyPart;

        foreach ($allAttachements as $attachment) {
            $bodies[] = [
                "type" => $attachment["type"],
                "subtype" => $attachment["subtype"],
                "encoding" => ENCBINARY,
                "description" => $attachment["title"],
                "contents.data" => $attachment["data"]
            ];
        }

        return imap_mail_compose($envelope, $bodies);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }


    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function getScore(): ?array
    {
        return $this->score;
    }


    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    // see imap-mail-compose for type and subtype detailed reference
    public function addAttachement(string $title, string $data, int $type = TYPEAPPLICATION, string $subtype = 'octet-stream'): SerializationMessage
    {
        $this->attachements[] = new SerializationAttachement(
            [ 'title' => $title, 'data' => $data, 'type' => $type,
              'subtype' => $subtype
            ]
        );
        return $this;
    }

    public function addAttachementLink(string $title, string $url): SerializationMessage
    {
        $this->attachementLinks[] = new SerializationAttachement([
            'title' => "link to " . $title,
            'data' => "<a href=\"{$url}\">{$title}</a>",
            'type' => "text",
            'subtype' => "html"
        ]);

        return $this;
    }

    protected function createExtraHeaders(HierarchicConfigInterface $sourceConfig): array
    {
        $headers = [];

        if ($this->getUri() !== null) {
            $headers[] = Constants::IAM_HEADER_URI . ": " . $this->getUri();
        }

        if (!empty($this->getScore()[0]) or !empty($this->getScore()[1])) {
            $headers[] = Constants::IAM_HEADER_SCORE . ": " . implode(",", $this->getScore());
        }

        if ($sourceConfig->getOpt('add_statusline_header')) {
            $statusline = $this->generateStatusLineHeader($sourceConfig);
            if (strlen($statusline)) {
                $headers[] = Constants::IAM_HEADER_STATUSLINE . ": " . $this->generateStatusLineHeader($sourceConfig);
            }
        }

        return $headers;
    }


    protected function generateStatusLineHeader(HierarchicConfigInterface $sourceConfig): ?string
    {
        $statusline = "";

        $score = $this->getScore();
        if ($score !== null) {
            $statusline .= "\u{2764}" . $score[0] . " \u{26a1}" . $score[1] . " ";
        }

        if (count($this->attachements)) {
            $statusline .= "\u{1f4be} ";
        }

        return $statusline;
    }

    protected function getFormattedSubject(HierarchicConfigInterface $sourceConfig): string
    {
        $subject = $this->subject;

        if (! empty($sourceConfig->getOpt("change_subject_if_attachements"))) {
            if (count($this->attachements) or count($this->attachementLinks) or count($this->externalAttachements)) {
                $subject = "[A] " . $subject;
            }
        }
        if (! empty($sourceConfig->getOpt("change_subject_if_score"))) {
            if ($this->getScore() !== null) {
                $subject = "[" . implode(",", $this->getScore()) . "] " . $subject;
            }
        }

        $subject = MailHeaderProcessor::sanitizeSubjectHeader($subject);

        return $subject;
    }

    /**
     * This one is for comparing exisitng MIME file with not existing, but
     * that where this message will be serialized into.
     */
    public function getTranslatedMIMEHeader(string $header, HierarchicConfigInterface $sourceConfig): string
    {
        if ($header === Constants::IAM_HEADER_STATUSLINE) {
            return $this->generateStatusLineHeader($sourceConfig);
        } elseif ($header === "subject") {
            return $this->getFormattedSubject($sourceConfig);
        } else {
            throw new \Exception("Unsupported header $header");
        }
    }

    public function getExternalAttachements(): array
    {
        return $this->externalAttachements;
    }

    public function setExternalAttachements(array $externalAttachements): void
    {
        $this->externalAttachements = $externalAttachements;
    }

    public function getCorrData() {
        return $this->corrData;
    }

    public function setCorrData($corrData) {
        $this->corrData = $corrData;
        return $this;
    }
}
