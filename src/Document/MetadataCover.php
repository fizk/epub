<?php

namespace Epub\Document;

use Epub\Document\MetadataInterface;
use DOMElement;

class MetadataCover implements MetadataInterface
{
    private string $cover;

    public function __construct(string $cover = 'cover')
    {
        $this->cover = $cover;
    }

    public function append(DOMElement $element): void
    {
        $publisherElement = $element->ownerDocument->createElement('meta');
        $publisherElement->setAttribute('name', 'cover');
        $publisherElement->setAttribute('content', $this->cover);
        $element->appendChild($publisherElement);

    }
}