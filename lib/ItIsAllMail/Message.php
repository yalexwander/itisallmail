<?php

namespace ItIsAllMail;

use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Message as MIMEMessage;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;
use Symfony\Component\Mime\Part\TextPart;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Header\DateHeader;

class Message
{
    protected $subject;
    protected $from;
    protected $parent;
    protected $created;
    protected $id;
    protected $body;
    protected $thread;
    protected $attachements = [];

    // this is list of extra headers, that can be useful in many places
    protected $extraHeaders = [
        'mentions', 'likes', 'dislikes', 'reference'
    ];

    // maximal length of subject
    protected $subjectMaxChars = 128;

    // list of all users of given site/network/messenger mention in this message
    protected $mentions;

    // count of likes if presented
    protected $likes;

    // count of dislikes if presented
    protected $dislikes;

    // list of message IDs this message references to
    protected $reference;


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
    }

    public function toMIMEString(): string
    {
        $headers = (new Headers())
            ->addMailboxListHeader('To', [ $this->thread ])
            ->addTextHeader('Subject', $this->getSubject())
            ->addDateHeader('Date', $this->created)
            ->addIdHeader('Message-id', [ $this->getId() ])
            ->addMailboxListHeader('From', [ $this->from ]);

        $this->setExtraHeaders($headers);

        if ($this->parent !== null) {
            $headers->addIdHeader('References', [ $this->getParentId() ]);
        }

        $body = new AlternativePart(
            new TextPart(
                $this->body
            ),
            ... $this->attachements
        );

        $message = new MIMEMessage($headers, $body);

        return $message->toString();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSubject(): string
    {
        if (mb_strlen($this->subject) > $this->subjectMaxChars) {
            return mb_substr($this->subject, 0, $this->subjectMaxChars) . "...";
        } else {
            return $this->subject;
        }
    }

    public function getParentId(): string
    {
        return $this->parent;
    }

    public function addAttachement(string $title, string $data): Message
    {
        if (! is_array($this->attachements)) {
            $this->attachements = [];
        }

        $this->attachements[] = new DataPart($data, $title, null);
        return $this;
    }


    protected function setExtraHeaders(Headers $headers): void
    {
        // if (count($this->mentions)) {
        //     $mentionHeader = "";
        //     foreach ($this->mentions as $mention) {
        //         $mentionHeader .=
        //     }
        // }
    }
}
