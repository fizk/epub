<?php

namespace Epub;

use Epub\Resource\ResourceInterface;
use RecursiveIterator;

interface ContainerInterface
{
    public function save(RecursiveIterator $iterator): void;

    public function addResource($content, string $mimetype, string $extension): string;

    public function encodeContentUri(ResourceInterface $resource): string;
}