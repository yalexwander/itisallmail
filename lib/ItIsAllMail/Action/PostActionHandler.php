<?php

namespace ItIsAllMail\Action;

use ItIsAllMail\PosterDriverFactory;
use ItIsAllMail\PostingQueue;

class PostActionHandler {
    protected $appConfig;

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function process(string $arg, string $rawMessage, array $msg) : int {
        $result = 1;

        if (! empty($this->appConfig["use_posting_queue"])) {
            $queue = new PostingQueue($this->appConfig);
            $result = $queue->add($rawMessage);
        } else {
            $transferFilename = tempnam(sys_get_temp_dir(), "iam-post-");
            file_put_contents($transferFilename, $rawMessage);

            $execString = "php \""  . __DIR__ . DIRECTORY_SEPARATOR
                . ".." . DIRECTORY_SEPARATOR
                . ".." . DIRECTORY_SEPARATOR
                . ".." . DIRECTORY_SEPARATOR
                . "scripts" . DIRECTORY_SEPARATOR . "poster.php\""
                . " -m \"" . $transferFilename . "\"";
            
            system($execString);
            unlink($transferFilename);
            
        }

        exit(1);
        return $result;
    }

}
