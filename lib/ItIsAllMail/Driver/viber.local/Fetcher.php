<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\DriverCommon\AbstractFetcherDriver;
use ItIsAllMail\HtmlToText;
use ItIsAllMail\CoreTypes\SerializationMessage;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\Browser;
use ItIsAllMail\Utils\MailHeaderProcessor;
use ItIsAllMail\Utils\URLProcessor;
use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDom;
use voku\helper\SimpleHtmlDomInterface;
use ItIsAllMail\CoreTypes\Source;

class ViberLocalFetcher extends AbstractFetcherDriver implements FetchDriverInterface
{
    protected $crawler;
    protected $driverCode = "viber.local";

    /**
     * Return array of all posts in thread, including original article
     */
    public function getPosts(Source $source): array
    {
        $posts = [];

        $dbFile = preg_replace('/' . $this->getCode() . ':/', '', $source["url"] . DIRECTORY_SEPARATOR . "viber.db");
        $db = new \SQLite3($dbFile);

        // first create fake messages for thread starters from chats
        $query = 'SELECT ci.ChatID, ci."Name" FROM ChatInfo ci';

        $result = $db->query($query);

        while (false !== $row = $result->fetchArray()) {

            $subject = "chat";
            if (! empty($row["Name"])) {
                $subject  = "chat \"{$row["Name"]}\"";
            }
            else {
                $stmt = $db->prepare(
                    'SELECT c.ClientName, c."Number" FROM Contact c INNER JOIN ChatRelation cr ON c.ContactID = cr.ContactID WHERE cr.ChatID = :cid AND cr.ContactID <> 1 LIMIT 1');
                $stmt->bindParam(':cid', $row["ChatID"]);
                $contactData = $stmt->execute()->fetchArray();

                if (empty($contactData["Number"]) and empty($contactData["ClientName"])) {
                    continue;
                }

                $subject = "chat \"{$contactData["ClientName"]} {$contactData["Number"]}\"";

            }
            
            $posts[] = new SerializationMessage([
                "from" => "chat" . $row["ChatID"] . "@" . $this->getCode(),
                "subject" => $subject,
                "parent" => null,
                "created" => new \DateTime('2000-01-01'),
                "id" => "chat" . $row["ChatID"] . "@" . $this->getCode(),
                "body" => "",
                "thread" => "chat" . $row["ChatID"] . "@" . $this->getCode(),
                "uri" => $source["url"] . "#chat_" . $row["ChatID"],
            ]);
        }
        // now collect actual messages from events
        $query = 'SELECT e.EventID, e."TimeStamp", m.Subject, m.Info, m.Body, ci.Name, ci.ChatID, co."Number", co.ClientName,
                   e."Type"
                   FROM Events e
                   INNER JOIN Messages m ON e.EventID = m.EventID INNER JOIN ChatInfo ci ON e.ChatID = ci.ChatID
                   INNER JOIN Contact co ON e.ContactID = co.ContactID;';

        $result = $db->query($query);

        while (false !== $row = $result->fetchArray()) {

            // skip likes or what is it
            if ($row["Type"] === 3) {
                continue;
            }
            
            $from = $row["Number"] . "@" . $this->getCode();
            if (! empty($row["ClientName"])) {
                $from = $row["ClientName"] . '<' . $from . '>' ;
            }

            $parent = "chat" . $row["ChatID"];
            $created = new \DateTime('@' . intval($row["TimeStamp"] / 1000));
            $thread = $row["ChatID"];

            $subject = "";
            $body = "";
            if (!empty($row["Subject"])) {
                $subject = $row["Subject"];
                $body = $row["Subject"];
            }
            elseif (!empty($row["Body"])) {
                $subject = $row["Body"];
                $body = $row["Body"];
            }

            if (strlen($row["Info"]) > 2) {
                $info = json_decode($row["Info"], true);
                if (! empty($info["quote"]["text"])) {
                    $body = ">" . $info["quote"]["text"] . "\n\n" . $body;
                }
             
                if (! empty($info["fileInfo"])) {
                    $subject = "[ATTACHEMENT]";
                }
            }
            
            $comment = new SerializationMessage([
                "from" => $from,
                "subject" => $subject,
                "parent" => $parent . "@" . $this->getCode(),
                "created" => $created,
                "id" => $row["EventID"] . "@" . $this->getCode(),
                "body" => $body,
                "thread" => $thread  . "@" . $this->getCode(),
                "uri" => $source["url"] . "#event_" . $row["EventID"],
            ]);

            $posts[] = $comment;
        }



        return $posts;
    }
}
