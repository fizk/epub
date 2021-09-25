<?php

namespace Epub\Document;

use Epub\Document\MetadataInterface;
use DOMElement;

class MetadataAuthor implements MetadataInterface
{
    private string $name;
    private string $fileAs;
    private ?string $alt = null;
    private ?string $altLang = null;

    public function __construct(string $name, string $fileAs, ?string $alt = null, ?string $altLang = null)
    {
        $this->name = $name;
        $this->fileAs = $fileAs;
        $this->alt = $alt;
        $this->altLang = $altLang;
    }

    public function append(DOMElement $element): void
    {
    // <dc:creator id="creator">Haruki Murakami</dc:creator>
    // <meta refines="#creator" property="role" scheme="marc:relators" id="role">aut</meta>
    // <meta refines="#creator" property="alternate-script" xml:lang="ja">村上 春樹</meta>
    // <meta refines="#creator" property="file-as">Murakami, Haruki</meta>
        $creatorId = 'c'.md5(rand(0, 100));

        $creatorElement = $element->ownerDocument->createElement('dc:creator', $this->name);
        $creatorElement->setAttribute('id', $creatorId);
        $element->appendChild($creatorElement);

        $creatorRoleElement = $element->ownerDocument->createElement('meta', 'aut');
        $creatorRoleElement->setAttribute('refines', '#'. $creatorId);
        $creatorRoleElement->setAttribute('property', 'role');
        $creatorRoleElement->setAttribute('scheme', 'marc:relators');
        $element->appendChild($creatorRoleElement);

        $creatorFileAsElement = $element->ownerDocument->createElement('meta', $this->fileAs);
        $creatorFileAsElement->setAttribute('refines', '#'. $creatorId);
        $creatorFileAsElement->setAttribute('property', 'file-as');
        $element->appendChild($creatorFileAsElement);

        if($this->alt) {
            $creatorFileAsElement = $element->ownerDocument->createElement('meta', $this->alt);
            $creatorFileAsElement->setAttribute('refines', '#'. $creatorId);
            $creatorFileAsElement->setAttribute('property', 'alternate-script');
            $creatorFileAsElement->setAttribute('xml:lang', $this->altLang ?: 'en');
            $element->appendChild($creatorFileAsElement);

        }
    }
}