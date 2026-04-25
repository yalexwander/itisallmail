<?php

require_once("scripts" . DIRECTORY_SEPARATOR . "includes.php");

function spawnServer(): int {
    $pid = pcntl_fork();

    $serverCommand = "php -S localhost:" . $_ENV['IAM_FUNC_TEST_SERVER_PORT'] . " -R iam-server-ps-flag";

    if (! $pid) {
        system("cd tests/func/server_root && $serverCommand");
        exit(0);
    }
    else {
        sleep(1);
        $pid = 0;
        exec('ps aux', $output, $retval);

        foreach ($output as $line) {
            if (str_contains($line, $serverCommand)) {
                $pid = mb_split('\s+', $line);
                $pid = $pid[1];
            }
        }

        if (! $pid) {
            die("no HTTP server running found");
        }

        return $pid;
    }
}


function createTempMailbox(): string {
    $tempDir = tempnam(sys_get_temp_dir(), "iam-test-mboxes");
    unlink($tempDir);

    if (is_dir($tempDir)) {
        die("Dir $tempDir already exists");
    }

    mkdir($tempDir);
    return $tempDir;
}


$testEnv = [
    'mailbox_base_dir' => createTempMailbox(),
    'http_server_pid' => spawnServer()
];

$testEnvFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "iam-test-data.json";
file_put_contents( $testEnvFile, json_encode($testEnv, true) );

