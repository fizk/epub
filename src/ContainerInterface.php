<?php

namespace Epub;

use RecursiveIterator;

interface ContainerInterface {
    public function save(RecursiveIterator $iterator): void;

    public function addResource($content, string $mimetype, string $extension): string;
}