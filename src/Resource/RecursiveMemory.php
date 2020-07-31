<?php

namespace Epub\Resource;

use RecursiveIterator;

class RecursiveMemory implements RecursiveIterator {

    private array $children = [];
    private int $index = 0;

    public function __construct (array $directory) {
        $this->index = 0;
        $this->children = $directory;
    }

    public function getChildren(): RecursiveIterator {
        return new RecursiveMemory($this->children[$this->index]->getChildren());
    }

    public function hasChildren() : bool {
        return \is_array($this->children[$this->index]->getChildren());
    }

    public function current() {
        return $this->children[$this->index];
    }

    public function key() {
        return $this->index;
    }

    public function next() {
        ++$this->index;
    }

    public function rewind() {
        $this->index = 0;
    }

    public function valid() {
        return isset($this->children[$this->index]);
    }
}