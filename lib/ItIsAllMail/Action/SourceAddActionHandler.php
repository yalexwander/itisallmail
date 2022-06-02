<?php

namespace ItIsAllMail\Action;

class SourceAddActionHandler {
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function process(string $arg, array $msg) {
        // $logxf=fopen("/tmp/zlog.txt","a");fputs($logxf,print_r($arg, true)  . "\n");fclose($logxf);chmod("/tmp/zlog.txt", 0666);
        // $logxf=fopen("/tmp/zlog.txt","a");fputs($logxf,print_r($msg, true)  . "\n");fclose($logxf);chmod("/tmp/zlog.txt", 0666);

        print_r(1);die();
    }
}
