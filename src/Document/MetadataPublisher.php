<?php

namespace Epub\Document;

use Epub\Document\MetadataInterface;
use DOMElement;

class MetadataPublisher implements MetadataInterface
{
    private string $publisher;

    public function __construct(string $publisher)
    {
        $this->publisher = $publisher;
    }

    public function append(DOMElement $element): void
    {
        $publisherElement = $element->ownerDocument->createElement(
            'dc:publisher',
            $this->publisher
        );
        $element->appendChild($publisherElement);

    }
}