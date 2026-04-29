<?php

$response = [
    [
        "from" => "me",
        "date" => "2026-01-01",
        "title" => "Hello",
        "data" => "greetings, my dear friend!",
        "thread" => "test",
        "id" => "msg1",
    ],
    [
        "from" => "notme",
        "date" => "2026-01-02",
        "title" => "Hello you too!",
        "data" => "How are you?",
        "thread" => "test",
        "id" => "msg22",
        "parent" => "msg1",
    ],

       
];

print json_encode($response);
