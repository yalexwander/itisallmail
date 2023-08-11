<?php

namespace ItIsAllMail;

use ItIsAllMail\CoreTypes\Source;

class SourceManager
{
    protected array $appConfig;

    protected array $sourceFiles;


    public function __construct(array $appConfig, ?array $sourceFiles = null)
    {
        $this->appConfig = $appConfig;

        $this->sourceFiles = $sourceFiles ?? [
            __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".."
            . DIRECTORY_SEPARATOR . "conf" . DIRECTORY_SEPARATOR . "sources.yml"
        ];
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
            $this->saveSources($sources);
            return 1;
        } else {
            return 0;
        }
    }

    public function deleteSource(Source $source): int
    {
        $sources = $this->getSources();

        $deleted = false;
        foreach ($sources as $sId => $existingSource) {
            if ($existingSource["url"] === $source["url"]) {
                unset($sources[$sId]);
                $deleted = true;
            }
        }

        if ($deleted) {
            $this->saveSources($sources);
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

        foreach ($this->sourceFiles as $sourceFile) {
            foreach (yaml_parse_file($sourceFile) as $config) {
                $config["source_file"] = $sourceFile;
                $sources[] = new Source($config);
            }
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

    protected function preSerialize(array $sources): array
    {
        foreach ($sources as &$s) {
            $s = $s->getArrayCopy();
            unset($s["source_file"]);
        }
        return $sources;
    }

    protected function saveSources(array $sources): void
    {
        $defaultFile = $this->sourceFiles[0];
        $fileMap = [];
        foreach ($this->sourceFiles as $sourceFile) {
            $fileMap[$sourceFile] = [];
        }

        foreach ($sources as $source) {
            if (isset($fileMap[ $source["source_file"] ])) {
                $fileMap[ $source["source_file"] ][] = $source;
            } else {
                $fileMap[ $defaultFile ][] = $source;
            }
        }

        foreach ($fileMap as $sourceFile => $fileSources) {
            yaml_emit_file($sourceFile, $this->preSerialize($fileSources));
        }
    }
}
