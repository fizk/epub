<?php
namespace Epub\Document\Navigation;

use Epub\Document\Navigation\NavigationInterface;
use DOMElement;
use DOMDocument;

class NavigationItem implements NavigationInterface {
    private DOMElement $element;
    private DOMDocument $doc;
    private ?DOMElement $rootListElement = null;

    public function __construct(DOMElement $element, ?DOMElement $rootListElement = null) {
        $this->element = $element;
        $this->doc = $element->ownerDocument;
        $this->rootListElement = $rootListElement;
    }

    public function addNavigation(string $title, string $location = null): NavigationItem {
         $this->rootListElement = $this->rootListElement ?: $this->doc->createElement('ol');

        $listItem = $this->doc->createElement('li');

        if ($location) {
            $linkElement = $this->doc->createElement('a');
            $linkElement->setAttribute('href', $location);
            $linkElement->appendChild($this->doc->createTextNode($title));
            $listItem->appendChild($linkElement);
        } else {
            $spanElement = $this->doc->createElement('span');
            $spanElement->appendChild($this->doc->createTextNode($title));
            $listItem->appendChild($spanElement);
        }

        $this->rootListElement->appendChild($listItem);
        $this->element->appendChild($this->rootListElement);

        return new NavigationItem($listItem);
    }
}