<?php

namespace Epub\Document\Navigation;

use Epub\Document\Navigation\NavigationItem;
use Epub\Document\Navigation\NavigationInterface;
use DOMDocument;
use DOMElement;
use DOMImplementation;

//@todo <nav epub:type="landmarks">
//@todo stylesheets

class XHTMLNavigation implements NavigationInterface
{
    private DOMDocument $dom;
    private DOMElement $rootElement;
    private DOMElement $headerElement;
    private DOMElement $bodyElement;
    private DOMElement $navElement;
    private DOMElement $navList;

    public function __construct()
    {
        $this->doc = new DOMDocument('1.0', 'UTF-8');

        $implementation = new DOMImplementation();
        $this->doc->appendChild($implementation->createDocumentType('html'));

        $this->root = $this->doc->createElement('html');
        $this->root->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        $this->root->setAttribute('xmlns:epub', 'http://www.idpf.org/2007/ops');

        $this->headerElement = $this->createHeader();
        $this->bodyElement = $this->createBody();
        $this->navElement = $this->createNavContainer();
        $this->navList = $this->createNavListContainer();

        $this->navElement->appendChild($this->navList);
        $this->bodyElement->appendChild($this->navElement);
        $this->root->appendChild($this->headerElement);
        $this->root->appendChild($this->bodyElement);
        $this->doc->appendChild($this->root);
    }

    public function addNavigation(string $title, string $location = null): NavigationItem
    {
        return (new NavigationItem($this->navElement, $this->navList))
            ->addNavigation($title, $location);
    }

    private function createHeader(): DOMElement
    {
        $headerElement = $this->doc->createElement('head');
        $metaCharsetElement = $this->doc->createElement('meta');
        $metaCharsetElement->setAttribute('charset', 'utf-8');

        $headerElement->appendChild($metaCharsetElement);

        return $headerElement;
    }

    private function createBody() : DOMElement
    {
        $bodyElement = $this->doc->createElement('body');
        return $bodyElement;
    }

    private function createNavContainer(string $type = 'toc'): DOMElement
    {
        $navElement = $this->doc->createElement('nav');
        $navElement->setAttribute('epub:type', $type);
        return $navElement;
    }

    private function createNavListContainer(): DOMElement
    {
        $navList = $this->doc->createElement('ol');

        return $navList;
    }

    public function __toString(): string
    {
        $this->doc->preserveWhiteSpace = false;
        $this->doc->formatOutput = true;
        return $this->doc->saveXML();
    }
}