<?php

namespace ItIsAllMail;

use ItIsAllMail\Message;

class PostingQueue {

    protected $appConfig;

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function add(array $msg) {
    }

    public function send(array $msg) : array {
        $posterDriverFactory = new PosterDriverFactory($this->appConfig);
        $poster = $posterDriverFactory->findPoster($msg);

        $result = $poster->post($msg);

        return $result;
    }
    
}
