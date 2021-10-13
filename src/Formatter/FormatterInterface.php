<?php

namespace Epub\Formatter;

use DOMDocument;
use Epub\ContainerInterface;
use Epub\Resource\ResourceInterface;
use RecursiveIterator;

interface FormatterInterface
{
    public function setWorkspace(ContainerInterface $workspace);

    public function formatChapterTitle(ResourceInterface $resource): string;

    public function formatPageTitle(ResourceInterface $resource): string;

    public function chapterTemplate(ResourceInterface $resource, RecursiveIterator $children): ?DOMDocument;

    public function pageTemplate(ResourceInterface $resource): ?DOMDocument;
}
