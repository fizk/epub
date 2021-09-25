<?php

namespace Epub\Document;

use Epub\Document\MetadataInterface;
use DateTime;
use DOMElement;

class MetadataPublishDate implements MetadataInterface
{
    private DateTime $date;

    public function __construct(DateTime $date)
    {
        $this->date = $date;
    }

    public function append(DOMElement $element): void
    {
        $dateElement = $element->ownerDocument->createElement(
            'dc:date',
            //2011-04-12T05:00:00+00:00
            $this->date->format('Y-m-d\TH:i+00:00')
        );
        $element->appendChild($dateElement);

    }
}