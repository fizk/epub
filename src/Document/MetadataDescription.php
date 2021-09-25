<?php

namespace Epub\Document;

use Epub\Document\MetadataInterface;
use DOMElement;

class MetadataDescription implements MetadataInterface
{
    private string $description;

    public function __construct(string $description)
    {
        $this->description = $description;
    }

    public function append(DOMElement $element): void
    {
        $descriptionElement = $element->ownerDocument->createElement(
            'dc:description',
            $this->description
        );
        $element->appendChild($descriptionElement);

    }
}