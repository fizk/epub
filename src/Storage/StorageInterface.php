<?php

namespace Epub\Storage;

interface StorageInterface
{
    public function createContainer(string $path): bool;

    public function createResource(string $path, string $content): bool;
}