<?php

namespace Epub\Formatter;

use DOMDocument;
use Epub\ContainerInterface;
use Epub\Resource\ResourceInterface;
use RecursiveIterator;

interface FormatterInterface {

    public function formatChapterTitle(string $title): string;

    public function formatPageTitle(string $title, ?string $content): string;

    public function setWorkspace(ContainerInterface $workspace);

    public function chapterTemplate(ResourceInterface $chapter, RecursiveIterator $children): ?DOMDocument;

    public function pageTemplate(ResourceInterface $page, string $content): ?DOMDocument;
}
