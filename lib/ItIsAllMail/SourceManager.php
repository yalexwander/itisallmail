<?php

namespace ItIsAllMail;
use ItIsAllMail\CoreTypes\Source;

class SourceManager
{
    protected array $appConfig;

    protected string $sourcesFile;


    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;

        $this->sourcesFile = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR
            . "conf" . DIRECTORY_SEPARATOR . "sources.yml";
    }

    public function addSource(Source $source): int
    {
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
            yaml_emit_file($this->sourcesFile, $this->preSerialize($sources));
            return 1;
        } else {
            return 0;
        }
    }

    public function deleteSource(Source $source): int
    {
        $sources = $this->getSources();

        $newSources = [];
        $deleted = false;
        foreach ($sources as $sId => $existingSource) {
            if ($existingSource["url"] !== $source["url"]) {
                $newSources[] = $existingSource;
            } else {
                $deleted = true;
            }
        }

        if ($deleted) {
            yaml_emit_file($this->sourcesFile, $this->preSerialize($newSources));
            return 1;
        } else {
            return 0;
        }
    }

    protected function validateSource(Source $source): void
    {
        if (empty($source["url"])) {
            throw new \Exception("url parameter is required");
        }
    }

    public function getSources(): array
    {
        $sources = [];
        
        foreach (yaml_parse_file($this->sourcesFile) as $config) {
            $sources[] = new Source($config);
        }
        
        return $sources;
    }

    public function getSourceById(string $url): ?Source
    {
        foreach ($this->getSources() as $source) {
            if ($source["url"] === $url) {
                return $source;
            }
        }

        return null;
    }

    protected function preSerialize(array $sources) : array {
        foreach ($sources as &$s) {
            $s = $s->getArrayCopy();
        }
        return $sources;
    }
}
