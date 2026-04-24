<?php

namespace ItIsAllMail;

use ItIsAllMail\Utils\UserConfigDir;
use ItIsAllMail\Interfaces\HierarchicConfigInterface;
use ItIsAllMail\Interfaces\Hooks\HookType;

class Hooks {
    protected array $appConfig;
    protected array $hooks;

    public function __construct()
    {
        $this->loadHooks();
    }

    protected function loadHooks() {
        $hooksDir = UserConfigDir::getDir() . DIRECTORY_SEPARATOR . "hooks";

        if (is_dir($hooksDir)) {
            foreach (scandir($hooksDir) as $hookFile) {
                if (! str_ends_with($hookFile, ".php")) {
                    continue;
                }
                list($className, $ext) = mb_split("\\.", $hookFile);

                $hookFilePath = $hooksDir . DIRECTORY_SEPARATOR . $hookFile;
                require_once($hookFilePath);
                
                if (! empty($this->hooks[$className])) {
                    die("Hook with name $className already declared");
                }

                $this->hooks[$className] = new $className();
            }
        }
    }

    public function runAvailableHooks(HookType $hookType, HierarchicConfigInterface $source, array $args) {
        if (empty($source->getOpt('hooks')) and ! is_array($source->getOpt('hooks'))) {
            return;
        }

        $pendingHooks = [];
        foreach ($source->getOpt('hooks') as $hookName) {
            if (empty($this->hooks[$hookName]) or ($hookType !== $this->hooks[$hookName]->getEventType()) ) {
                continue;
            }

            $pendingHooks[] = $hookName;
        }

        usort($pendingHooks, function($a, $b) {
            return ( $this->hooks[$a]->getPriority() - $this->hooks[$b]->getPriority() );
        });

        foreach ($pendingHooks as $hookName) {
            $this->hooks[$hookName]->run($args);
        }
    }
    
}
