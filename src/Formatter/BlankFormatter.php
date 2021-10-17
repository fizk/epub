<?php

namespace Epub\Formatter;

use Epub\ContainerInterface;
use Epub\Resource\ResourceInterface;
use RecursiveIterator;
use DOMDocument;

class BlankFormatter implements FormatterInterface
{
    public function formatChapterTitle(ResourceInterface $resource): string
    {
        return $resource->getName();
    }

    public function formatPageTitle(ResourceInterface $resource): string
    {
        return $resource->getName();
    }

    public function setWorkspace(ContainerInterface $workspace)  {}

    public function chapterTemplate(ResourceInterface $resource, RecursiveIterator $children): ?DOMDocument
    {
        return null;
    }

    public function pageTemplate(ResourceInterface $resource): ?DOMDocument
    {
        $dom = new DOMDocument();
        $htmlElement = $dom->createElement('html');
        $htmlElement->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        $bodyElement = $dom->createElement('body');
        $paragraphElement = $dom->createElement('p', "{$resource->getName()}\n{$resource->getContent()}");

        $bodyElement->appendChild($paragraphElement);
        $htmlElement->appendChild($bodyElement);
        $dom->appendChild($htmlElement);
        return $dom;
    }
}
