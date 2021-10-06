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
    }


    public function debug(): void
    {
        print <<<EOT
        subject: {$this->subject}
        from: {$this->from}
        parent: {$this->parent}
        created: {$this->created->format("Y-m-d H:i:s")}
        id: {$this->id}
        body: {$this->body}
====\n
EOT;
    }

    public function toMIMEString(): string
    {
        $headers = (new Headers())
            ->addMailboxListHeader('To', [ $this->thread ])
            ->addTextHeader('Subject', $this->getSubject())
            ->addDateHeader('Date', $this->created)
            ->addIdHeader('Message-id', [ $this->getId() ])
            ->addMailboxListHeader('From', [ $this->from ]);

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
        return mb_substr($this->subject, 0, 64) . "...";
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
}
