<?php

namespace Epub\Formatter;

use Epub\ContainerInterface;
use Epub\Resource\ResourceInterface;
use SplFileInfo;
use RecursiveIterator;

class BlankFormatter implements FormatterInterface {

    public function formatChapterTitle(string $title): string {
        return $title;
    }

    public function formatPageTitle(string $title): string {
        return $title;
    }

    public function setWorkspace(ContainerInterface $workspace)  {}

    public function chapterTemplate(ResourceInterface $chapter, RecursiveIterator $children): ?string {
        return null;
    }

    public function pageTemplate(ResourceInterface $page, string $content): ?string {
        return "{$page->getName()}\n{$content}";
    }
}
