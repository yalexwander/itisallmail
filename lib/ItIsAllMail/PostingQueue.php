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
    
}
