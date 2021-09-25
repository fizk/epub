<?php

namespace Epub\Resource;
use  Epub\Resource\ResourceInterface;

class ResourceMemory implements ResourceInterface
{
    protected string $name;
    protected ?string $content;
    protected ?array $children;

    public function __construct(string $name, ?string $content = null, ?array $children = null)
    {
        $this->name = $name;
        $this->content = $content;
        $this->children = $children;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getChildren(): ?array
    {
        return $this->children;
    }
}