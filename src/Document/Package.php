<?php

namespace Epub\Document;

use DOMDocument;
use DOMElement;
use DateTime;

class Package {
    private DOMDocument $dom;
    private DOMElement $rootElement;
    private DOMElement $metadataElement;
    private DOMElement $manifestElement;
    private DOMElement $spineElement;
    private DOMElement $collectionElement;

    public function __construct(string $id, string $title, DateTime $date, ?string $lang = 'en') {
        $this->doc = new DOMDocument();
        $this->root = $this->createPackageElement($lang);
        $this->metadataElement = $this->createMetadataElement($id, $title, $date, $lang);
        $this->manifestElement = $this->createManifestElement();
        $this->spineElement = $this->createSpineElement();

        $this->doc->appendChild($this->root);
        $this->root->appendChild($this->metadataElement);
        $this->root->appendChild($this->manifestElement);
        $this->root->appendChild($this->spineElement);
    }

    public function addManifest(string $href, string $id, string $mediaType = 'application/xhtml+xml', array $properties = []): self {
        $itemElement = $this->doc->createElement('item');
        $itemElement->setAttribute('href', $href);
        $itemElement->setAttribute('id', $id);
        $itemElement->setAttribute('media-type', $mediaType);
        if (\count($properties) > 0) $itemElement->setAttribute('properties', \implode(' ', $properties));

        $this->manifestElement->appendChild($itemElement);

        return $this;
    }

    public function addSpine(string $idref, string $linear = 'yes'): self {
        $itemElement = $this->doc->createElement('itemref');
        $itemElement->setAttribute('idref', $idref);
        $itemElement->setAttribute('linear', $linear);

        $this->spineElement->appendChild($itemElement);

        return $this;
    }

    // @todo add optional meta tags https://www.w3.org/publishing/epub3/epub-packages.html#sec-opf-dcmes-optional

    private function createPackageElement(string $xmllang = null, string $dir = 'ltr' , string $id = null, string $prefix = null): DOMElement {
        $packageElement = $this->doc->createElement('package');
        $packageElement->setAttribute('xmlns', 'http://www.idpf.org/2007/opf');
        $packageElement->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        $packageElement->setAttribute('version', '3.0');
        $packageElement->setAttribute('unique-identifier', 'pub-identifier');
        if ($dir !== null) $packageElement->setAttribute('dir', $dir);
        if ($xmllang !== null) $packageElement->setAttribute('xml:lang', $xmllang);
        if ($prefix !== null) $packageElement->setAttribute('prefix', $prefix);
        if ($id !== null) $packageElement->setAttribute('id', $id);

        return $packageElement;
    }

    private function createMetadataElement(string $id, string $title, DateTime $date, string $language = null): DOMElement {
        $metadataElement = $this->doc->createElement('metadata');

        $dcIdentifierElement = $this->doc->createElement('dc:identifier');
        $dcIdentifierElement->setAttribute('id', 'pub-identifier');
        $dcIdentifierElement->appendChild(
            $this->doc->createTextNode("urn:uuid:{$id}")
        );

        $dcTitleElement = $this->doc->createElement('dc:title');
        $dcTitleElement->setAttribute('id', 'pub-title');
        $dcTitleElement->appendChild(
            $this->doc->createTextNode($title)
        );

        $dcLanguageElement = $this->doc->createElement('dc:language');
        $dcLanguageElement->setAttribute('id', 'pub-language');
        $dcLanguageElement->appendChild(
            $this->doc->createTextNode($language)
        );

        $metadataElement->appendChild($dcIdentifierElement);
        $metadataElement->appendChild($dcTitleElement);
        if ($language !== null) $metadataElement->appendChild($dcLanguageElement);

        //<meta property="dcterms:modified">2012-10-24T15:30:00Z</meta>
        $metaModifiedElement = $this->doc->createElement('meta');
        $metaModifiedElement->setAttribute('property', 'dcterms:modified');
        $metaModifiedElement->appendChild($this->doc->createTextNode($date->format('Y-m-d\TH:i:s\Z')));

        $metadataElement->appendChild($metaModifiedElement);

        // @todo meta tag
        return $metadataElement;
    }

    private function createSpineElement(): DOMElement {
        $spineElement = $this->doc->createElement('spine');

        return $spineElement;
    }

    private function createManifestElement(): DOMElement {
        $manifestElement = $this->doc->createElement('manifest');

        return $manifestElement;
    }

    public function __toString(): string {
        return $this->doc->saveXML();
    }
}