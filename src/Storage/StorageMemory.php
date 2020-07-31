<?php

namespace Epub\Storage;

class StorageMemory implements StorageInterface {

    private array $memory = [];

    public function createContainer(string $path): bool {
        $pathParts = $this->pathToArray($path);
        $currentElement = &$this->memory;

        foreach($pathParts as $item) {
            if (array_key_exists($item, $currentElement)) {
                $currentElement = &$currentElement[$item];
            } else {
                $currentElement[$item] = [];
                $currentElement = &$currentElement[$item];
            }
        }

        return true;
    }

    public function createResource(string $path, string $content): bool {
        $pathParts = $this->pathToArray($path);
        $currentElement = &$this->memory;
        $resourceName = array_pop($pathParts);

        foreach($pathParts as $item) {
            if (array_key_exists($item, $currentElement)) {
                $currentElement = &$currentElement[$item];
            } else {
                $currentElement[$item] = [];
                $currentElement = &$currentElement[$item];
            }
        }
        $currentElement[$resourceName] = $content;

        return true;
    }

    public function getMemory(): array {
        return $this->memory;
    }

    private function pathToArray($path): array {
        $pathParts = \explode('/', $path);
        return array_filter($pathParts, function ($item) {
            return \strlen(trim($item)) > 0;
        });
    }
}