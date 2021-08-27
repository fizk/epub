<?php

namespace Epub\Formatter;

use Epub\ContainerInterface;
use Epub\Resource\ResourceInterface;
use RecursiveIterator;
use DOMDocument;

class BlankFormatter implements FormatterInterface {

    public function formatChapterTitle(string $title): string {
        return $title;
    }

    public function formatPageTitle(string $title, ?string $content): string {
        return $title;
    }

    public function setWorkspace(ContainerInterface $workspace)  {}

    public function chapterTemplate(ResourceInterface $chapter, RecursiveIterator $children): ?DOMDocument {
        return null;
    }

    public function pageTemplate(ResourceInterface $page, string $content): ?DOMDocument {
        $dom = new DOMDocument();
        $dom->appendChild(
            $dom->createElement('html', "{$page->getName()}\n{$content}")
        );
        return $dom;
    }
}
