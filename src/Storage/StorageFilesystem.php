<?php

namespace Epub\Storage;
use Epub\Storage\StorageInterface;

class StorageFilesystem implements StorageInterface
{
    private $root;

    public function __construct(string $root)
    {
        $this->root = $root;
    }

    public function createContainer(string $path): bool
    {
        @mkdir("{$this->root}/{$path}");
        return true;
    }

    public function createResource(string $path, string $content): bool
    {
        file_put_contents("{$this->root}/{$path}", $content);
        return true;
    }
}