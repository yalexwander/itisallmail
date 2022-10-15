<?php

namespace ItIsAllMail\CoreTypes;

use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Interfaces\HierarchicConfigInterface;
use ItIsAllMail\Constants;
use ItIsAllMail\CoreTypes\SerializationAttachement;

/**
 * This class represents internal message. It does not maps directly to MIME
 * or any other data structure. It is just a bridge to define features and
 * pieces of information you can found in modern messengers and how it can be
 * mapped to MIME.
 */

class SerializationMessage
{
    protected $subject;
    protected $from;
    protected $parent;
    protected $created;
    protected $id;
    protected $body;
    protected $thread;
    protected $attachements = [];
    protected $attachementLinks = [];

    // this is list of extra headers, that can be useful in many places
    protected $extraHeaders = [
        'mentions', 'score', 'reference', 'uri'
    ];

    // maximal length of subject
    protected $subjectMaxChars = 128;

    // list of all users of given site/network/messenger mention in this message
    protected $mentions;

    // count of likes and dislikes if presented
    protected $score;

    // list of message IDs this message references to
    protected $reference;

    // URI that specifies link of filepath, ot unique ID that can be directly
    // converted to message source
    protected $uri;

    // needed for rendering purposes when we know attachements could be
    // already downloaded
    protected $externalAttachements = [];

    public function __construct(array $source)
    {
        $this->subject = $source["subject"];
        $this->from = $source["from"];
        $this->parent = $source["parent"];
        $this->created = $source["created"];
        $this->id = $source["id"];
        $this->body = $source["body"];
        $this->thread = $source["thread"];
        $this->attachements = $source["attachements"] ?? [];
        $this->mentions = $source["mentions"] ?? [];
        $this->uri = $source["uri"] ?? null;
        $this->score = $source["score"] ?? null;
    }


    public function toMIMEString(HierarchicConfigInterface $sourceConfig): string
    {
        Debug::debug("Trying convert to MIME:");
        Debug::debug(Debug::dumpMessage($this));
        $envelope = [];
        $envelope["from"]= $this->from;
        $envelope["to"]  = $this->thread;
        $envelope["date"]  = $this->created->format("D, d M Y H:i:s O");
        $envelope["subject"]  = $this->getFormattedSubject($sourceConfig);
        $envelope["message_id"]  = "<" . $this->getId() . ">";

        $envelope["custom_headers"] = $this->createExtraHeaders($sourceConfig);
        
        if ($this->parent !== null) {
            $envelope["custom_headers"][] = "References: <" . $this->getParent() . ">";
        }

        $bodies = [];
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

    public function addAttachement(string $title, string $data): SerializationMessage
    {
        $this->attachements[] = new SerializationAttachement(
            [ 'title' => $title, 'data' => $data, 'type' => 'application',
              'subtype' => 'octet-stream'
              
            ]);
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

        if (!empty($score)) {
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


    protected function generateStatusLineHeader(HierarchicConfigInterface $sourceConfig) : ?string {
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

        if (mb_strlen($subject) > $this->subjectMaxChars) {
            $subject = mb_substr($subject, 0, $this->subjectMaxChars) . "...";
        }

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

        $subject = preg_replace('/( +)|([\r\n])/', ' ', $subject);

        return $subject;
    }

    /**
     * This one is for comparing exisitng MIME file with not existing, but
     * that where this message will be serialized into.
     */
    public function getTranslatedMIMEHeader(string $header, HierarchicConfigInterface $sourceConfig): string {
        if ($header === Constants::IAM_HEADER_STATUSLINE) {
            return $this->generateStatusLineHeader($sourceConfig);
        }
        elseif ($header === "subject") {
            return $this->getFormattedSubject($sourceConfig);
        }
        else {
            throw new \Exception("Unsupported header $header");
        }
    }

    public function getExternalAttachements() : array {
        return $this->externalAttachements;
    }

    public function setExternalAttachements($externalAttachements) : void {
        $this->externalAttachements = $externalAttachements;
    }
}
