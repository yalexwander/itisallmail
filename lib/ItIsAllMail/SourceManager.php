<?php

namespace ItIsAllMail;

class SourceManager {

    protected $appConfig;

    protected $sourcesFile;
    
    
    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;

        $this->sourcesFile = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR
            . "conf" . DIRECTORY_SEPARATOR . "sources.yml";
    }

    public function addSource(array $source) : int {
        $this->validateSource($source);

        $sources = $this->getSources();

        $sourceExists = false;
        foreach ($sources as $existingSource) {
            if ($existingSource["url"] === $source["url"]) {
                $existingSource = $source;
                $sourceExists = true;
                break;
            }
        }

        if (! $sourceExists) {
            array_push($sources, $source);
            yaml_emit_file($this->sourcesFile, $sources);
            return 1;
        } else {
            return 0;
        }
    }

    public function deleteSource(array $source) : int {
        $sources = $this->getSources();

        $newSources = [];
        $deleted = false;
        foreach ($sources as $sId => $existingSource) {
            if ($existingSource["url"] !== $source["url"]) {
                $newSources[] = $existingSource;
            }
            else {
                $deleted = true;
            }
        }

        if ($deleted) {
            yaml_emit_file($this->sourcesFile, $newSources);
            return 1;
        }
        else {
            return 0;
        }
    }

    protected function validateSource(array $source) : void {
        if (empty($source["url"])) {
            throw new \Exception("url parameter is required");
        }
    }

    public function getSources() : array {
        return yaml_parse_file($this->sourcesFile);
    }

    public function getSourceById(string $url) : ?array {
        foreach ($this->getSources() as $source) {
            if ($source["url"] === $url) {
                return $source;
            }
        }

        return null;
    }
}
