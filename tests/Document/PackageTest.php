<?php

use Epub\Document\MetadataAuthor;
use Epub\Document\MetadataCover;
use Epub\Document\MetadataDescription;
use Epub\Document\MetadataPublishDate;
use Epub\Document\MetadataPublisher;
use PHPUnit\Framework\TestCase;
use Epub\Document\Package;

class PackageTest extends TestCase {
    public function testBasic() {
        $expected = '<?xml version="1.0"?>
            <package dir="ltr" unique-identifier="pub-identifier" version="3.0" xml:lang="en"
                xmlns="http://www.idpf.org/2007/opf" xmlns:dc="http://purl.org/dc/elements/1.1/">
                <metadata>
                    <dc:identifier id="pub-identifier">urn:uuid:A1B0D67E-2E81-4DF5-9E67-A64CBE366809</dc:identifier>
                    <dc:title id="pub-title">some title</dc:title>
                    <dc:language id="pub-language">en</dc:language>
                    <meta property="dcterms:modified">2001-01-01T00:00:00Z</meta>
                </metadata>
                <manifest/>
                <spine page-progression-direction="ltr"/>
            </package>
        ';

        $actual = (string) new Package('A1B0D67E-2E81-4DF5-9E67-A64CBE366809', 'some title', new DateTime('2001-01-01'));

        $this->assertXmlStringEqualsXmlString($expected, $actual);
    }

    public function testManifest() {
        $expected = '<?xml version="1.0"?>
            <package dir="ltr" unique-identifier="pub-identifier" version="3.0" xml:lang="en"
                xmlns="http://www.idpf.org/2007/opf" xmlns:dc="http://purl.org/dc/elements/1.1/">
                <metadata>
                    <dc:identifier id="pub-identifier">urn:uuid:A1B0D67E-2E81-4DF5-9E67-A64CBE366809</dc:identifier>
                    <dc:title id="pub-title">some title</dc:title>
                    <dc:language id="pub-language">en</dc:language>
                    <meta property="dcterms:modified">2001-01-01T00:00:00Z</meta>
                </metadata>
                <manifest>
                    <item href="href" id="id" media-type="application/xhtml+xml" />
                    <item href="href" id="id" media-type="image/jpeg" />
                    <item href="href" id="id" media-type="image/jpeg" properties="property" />
                </manifest>
                <spine page-progression-direction="ltr"/>
            </package>
        ';
        $actual = (string) (new Package('A1B0D67E-2E81-4DF5-9E67-A64CBE366809', 'some title', new DateTime('2001-01-01')))
            ->addManifest('href', 'id')
            ->addManifest('href', 'id', 'image/jpeg')
            ->addManifest('href', 'id', 'image/jpeg', ['property']);

        $this->assertXmlStringEqualsXmlString($expected, $actual);
    }

    public function testSpine() {
        $expected = '<?xml version="1.0"?>
            <package dir="ltr" unique-identifier="pub-identifier" version="3.0" xml:lang="en"
                xmlns="http://www.idpf.org/2007/opf" xmlns:dc="http://purl.org/dc/elements/1.1/">
                <metadata>
                    <dc:identifier id="pub-identifier">urn:uuid:A1B0D67E-2E81-4DF5-9E67-A64CBE366809</dc:identifier>
                    <dc:title id="pub-title">some title</dc:title>
                    <dc:language id="pub-language">en</dc:language>
                    <meta property="dcterms:modified">2001-01-01T00:00:00Z</meta>
                </metadata>
                <manifest/>
                <spine page-progression-direction="ltr">
                    <itemref idref="idhref" linear="yes" />
                    <itemref idref="idhref" linear="no" />
                </spine>
            </package>
        ';
        $actual = (string) (new Package('A1B0D67E-2E81-4DF5-9E67-A64CBE366809', 'some title', new DateTime('2001-01-01')))
            ->addSpine('idhref')
            ->addSpine('idhref', 'no');

        $this->assertXmlStringEqualsXmlString($expected, $actual);
    }

    public function testMetadataElement()
    {
        $package = new Package('id', 'title', new DateTime('2000-01-01'));

        $dom = $package->toDom();
        $metadataElement = $dom->getElementsByTagName('metadata');

        $this->assertEquals(1, $metadataElement->count());
        $this->assertEquals('dc:identifier', $metadataElement->item(0)->childNodes->item(0)->nodeName);
        $this->assertEquals('dc:title', $metadataElement->item(0)->childNodes->item(1)->nodeName);
        $this->assertEquals('dc:language', $metadataElement->item(0)->childNodes->item(2)->nodeName);
        $this->assertEquals('meta', $metadataElement->item(0)->childNodes->item(3)->nodeName);
    }

    public function testManifestElement()
    {
        $package = new Package('id', 'title', new DateTime('2000-01-01'));
        $package->addManifest('href', 'id', 'media-type');

        $dom = $package->toDom();
        $manifestElement = $dom->getElementsByTagName('manifest');

        $this->assertEquals(1, $manifestElement->count());
        $this->assertEquals(1, $manifestElement->item(0)->childNodes->count());
        $this->assertEquals('href', $manifestElement->item(0)->childNodes->item(0)->getAttribute('href'));
        $this->assertEquals('id', $manifestElement->item(0)->childNodes->item(0)->getAttribute('id'));
        $this->assertEquals('media-type', $manifestElement->item(0)->childNodes->item(0)->getAttribute('media-type'));
    }

    public function testSpineElement()
    {
        $package = new Package('id', 'title', new DateTime('2000-01-01'));
        $package->addSpine('idref');
        $package->addSpine('idref', 'yes');
        $package->addSpine('idref', 'no');
        $package->addSpine('idref', 'no', ['some', 'prop']);

        $dom = $package->toDom();
        $spineElement = $dom->getElementsByTagName('spine');

        $this->assertEquals(1, $spineElement->count());
        $this->assertEquals(4, $spineElement->item(0)->childNodes->count());
        $this->assertEquals('idref', $spineElement->item(0)->childNodes->item(0)->getAttribute('idref'));

        $this->assertEquals('yes', $spineElement->item(0)->childNodes->item(0)->getAttribute('linear'));
        $this->assertEquals('yes', $spineElement->item(0)->childNodes->item(1)->getAttribute('linear'));
        $this->assertEquals('no', $spineElement->item(0)->childNodes->item(2)->getAttribute('linear'));

        $this->assertEquals('some prop', $spineElement->item(0)->childNodes->item(3)->getAttribute('properties'));

    }

    public function testAppendMetadataDescription()
    {
        $package = new Package('id', 'title', new DateTime('2000-01-01'));
        $package->addMetadata(new MetadataDescription('some description'));

        $dom = $package->toDom();
        $descriptionElement = $dom->getElementsByTagName('dc:description');

        $this->assertEquals(1, $descriptionElement->length);
        $this->assertEquals('some description', $descriptionElement->item(0)->nodeValue);
    }

    public function testAppendMetadataAuthor()
    {
        $package = new Package('id', 'title', new DateTime('2000-01-01'));
        $package->addMetadata(new MetadataAuthor('some name', 'name, some'));

        $dom = $package->toDom();
        $metadataElements = $dom->getElementsByTagName('metadata');

        $this->assertEquals('some name', $metadataElements->item(0)->childNodes->item(4)->nodeValue);
        $this->assertEquals('aut', $metadataElements->item(0)->childNodes->item(5)->nodeValue);
        $this->assertEquals('name, some', $metadataElements->item(0)->childNodes->item(6)->nodeValue);

        $id = $metadataElements->item(0)->childNodes->item(4)->getAttribute('id');
        $this->assertEquals("#{$id}", $metadataElements->item(0)->childNodes->item(5)->getAttribute('refines'));
        $this->assertEquals("#{$id}", $metadataElements->item(0)->childNodes->item(6)->getAttribute('refines'));
    }

    public function testAppendMetadataAuthorAlt()
    {
        $package = new Package('id', 'title', new DateTime('2000-01-01'));
        $package->addMetadata(new MetadataAuthor('some name', 'name, some', 'blah', 'is'));

        $dom = $package->toDom();
        $metadataElements = $dom->getElementsByTagName('metadata');

        $this->assertEquals('some name', $metadataElements->item(0)->childNodes->item(4)->nodeValue);
        $this->assertEquals('aut', $metadataElements->item(0)->childNodes->item(5)->nodeValue);
        $this->assertEquals('name, some', $metadataElements->item(0)->childNodes->item(6)->nodeValue);
        $this->assertEquals('blah', $metadataElements->item(0)->childNodes->item(7)->nodeValue);

        $id = $metadataElements->item(0)->childNodes->item(4)->getAttribute('id');
        $this->assertEquals("#{$id}", $metadataElements->item(0)->childNodes->item(5)->getAttribute('refines'));
        $this->assertEquals("#{$id}", $metadataElements->item(0)->childNodes->item(6)->getAttribute('refines'));
        $this->assertEquals("#{$id}", $metadataElements->item(0)->childNodes->item(7)->getAttribute('refines'));
    }

    public function testAppendMetadataAuthors()
    {
        $package = new Package('id', 'title', new DateTime('2000-01-01'));
        $package->addMetadata(new MetadataAuthor('Author One', 'One, Author'));
        $package->addMetadata(new MetadataAuthor('Author Two', 'Two, Author'));

        $dom = $package->toDom();
        $metadataElements = $dom->getElementsByTagName('metadata');

        $this->assertEquals('Author One', $metadataElements->item(0)->childNodes->item(4)->nodeValue);
        $this->assertEquals('aut', $metadataElements->item(0)->childNodes->item(5)->nodeValue);
        $this->assertEquals('One, Author', $metadataElements->item(0)->childNodes->item(6)->nodeValue);

        $id1 = $metadataElements->item(0)->childNodes->item(4)->getAttribute('id');
        $this->assertEquals("#{$id1}", $metadataElements->item(0)->childNodes->item(5)->getAttribute('refines'));
        $this->assertEquals("#{$id1}", $metadataElements->item(0)->childNodes->item(6)->getAttribute('refines'));

        $this->assertEquals('Author Two', $metadataElements->item(0)->childNodes->item(7)->nodeValue);
        $this->assertEquals('aut', $metadataElements->item(0)->childNodes->item(8)->nodeValue);
        $this->assertEquals('Two, Author', $metadataElements->item(0)->childNodes->item(9)->nodeValue);

        $id2 = $metadataElements->item(0)->childNodes->item(7)->getAttribute('id');
        $this->assertEquals("#{$id2}", $metadataElements->item(0)->childNodes->item(8)->getAttribute('refines'));
        $this->assertEquals("#{$id2}", $metadataElements->item(0)->childNodes->item(9)->getAttribute('refines'));
    }

    public function testAppendMetadataPublisher()
    {
        $package = new Package('id', 'title', new DateTime('2000-01-01'));
        $package->addMetadata(new MetadataPublisher('some publisher'));

        $dom = $package->toDom();
        $publisherElements = $dom->getElementsByTagName('dc:publisher');

        $this->assertTrue(true);

        $this->assertEquals(1, $publisherElements->length);
        $this->assertEquals('some publisher', $publisherElements->item(0)->nodeValue);
    }

    public function testAppendMetadataPublisDate()
    {
        $package = new Package('id', 'title', new DateTime('2000-01-01'));
        $package->addMetadata(new MetadataPublishDate(new DateTime('2000-02-02')));

        $dom = $package->toDom();
        $publisherElements = $dom->getElementsByTagName('dc:date');

        $this->assertEquals(1, $publisherElements->length);
        $this->assertEquals('2000-02-02T00:00+00:00', $publisherElements->item(0)->nodeValue);
    }

    public function testAppendMetadataCover()
    {
        $package = new Package('id', 'title', new DateTime('2000-01-01'));
        $package->addMetadata(new MetadataCover());

        $dom = $package->toDom();
        $coverElements = $dom->getElementsByTagName('meta');

        $this->assertGreaterThanOrEqual(1, $coverElements->length);
        $this->assertEquals('cover', $coverElements->item(1)->getAttribute('name'));
        $this->assertEquals('cover', $coverElements->item(1)->getAttribute('content'));
    }

    public function testAppendMetadataCustomCover()
    {
        $package = new Package('id', 'title', new DateTime('2000-01-01'));
        $package->addMetadata(new MetadataCover('custom-cover'));

        $dom = $package->toDom();
        $coverElements = $dom->getElementsByTagName('meta');

        $this->assertGreaterThanOrEqual(1, $coverElements->length);
        $this->assertEquals('cover', $coverElements->item(1)->getAttribute('name'));
        $this->assertEquals('custom-cover', $coverElements->item(1)->getAttribute('content'));
    }
}