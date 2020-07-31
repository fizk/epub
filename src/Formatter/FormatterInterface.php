<?php

namespace Epub\Formatter;

use Epub\ContainerInterface;
use Epub\Resource\ResourceInterface;
use RecursiveIterator;

interface FormatterInterface {

    public function format(string $content): string;

    public function formatChapterTitle(string $title): string;

    public function formatPageTitle(string $title): string;

    public function setWorkspace(ContainerInterface $workspace);

    public function chapterTemplate(ResourceInterface $chapter, RecursiveIterator $children): ?string;

    public function pageTemplate(ResourceInterface $page, string $content): ?string;
}
