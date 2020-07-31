<?php

namespace Epub\Resource;
use  Epub\Resource\ResourceInterface;

class ResourceMemory implements ResourceInterface {

    private string $name;
    private ?string $content;
    private ?array $children;

    public function __construct(string $name, ?string $content = null, ?array $children = null) {
        $this->name = $name;
        $this->content = $content;
        $this->children = $children;
    }
    public function getContent() {
        return $this->content;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getChildren() {
        return $this->children;
    }
}