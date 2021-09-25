<?php

namespace Epub\Document;

use DOMElement;

interface MetadataInterface
{
    public function append(DOMElement $element): void;
}