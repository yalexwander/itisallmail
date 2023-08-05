<?php

/**
 * see /neomutt/utils/register
 */

namespace ItIsAllMail\MUA;

class Register
{
    protected string $registerDir;

    public function __construct()
    {
        $this->registerDir = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR
            . ".." . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . ".register";
    }

    public function set(string $name, string $data): void
    {
        $outputRegisterFile = $this->registerDir . DIRECTORY_SEPARATOR . $name;

        file_put_contents(
            $outputRegisterFile,
            $data
        );
    }

    public function get(string $name): ?string
    {
        return file_get_contents($this->registerDir . DIRECTORY_SEPARATOR . $name);
    }
}
