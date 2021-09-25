<?php

namespace Epub\Resource;

use Epub\Resource\ResourceInterface;
use RecursiveIterator;
use SplFileInfo;

class RecursiveDirectory implements RecursiveIterator
{
    protected SplFileInfo $currentFileObject;
    protected array $children = [];
    protected int $index = 0;

    public function __construct ($directory)
    {
        $this->currentFileObject = new SplFileInfo($directory);
        $this->children = array_values(array_map(function ($item) use ($directory) {
            return new class ($directory . '/' .$item) extends SplFileInfo implements  ResourceInterface {
                public function getContent() {
                    return \file_get_contents($this->getRealPath());
                }

                public function getName(): string {
                    return $this->getFilename();
                }
            };
        }, array_diff(scandir($this->currentFileObject->getRealPath()), array('..', '.', '.DS_Store'))));
    }

    public function getChildren(): RecursiveIterator
    {
        return new RecursiveDirectory($this->children[$this->index]->getRealPath());
    }

    public function hasChildren() : bool
    {
        return $this->children[$this->index]->isDir();
    }

    public function current()
    {
        return $this->children[$this->index];
    }

    public function key()
    {
        return $this->index;
    }

    public function next()
    {
        $this->index = $this->index + 1;
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function valid ( )
    {
        return count($this->children) > $this->index;
    }
}