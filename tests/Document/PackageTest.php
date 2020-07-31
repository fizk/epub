<?php


use PHPUnit\Framework\TestCase;
use Epub\Document\Package;

class ContentGeneratorTest extends TestCase {
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
                <spine/>
            </package>
        ';
        //public function __construct(string $id, string $title, DateTime $date, ?string $lang = 'en')
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
                <spine/>
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
                <spine>
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
}