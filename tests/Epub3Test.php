<?php

use PHPUnit\Framework\TestCase;
use Epub\Epub3;
use Epub\Storage\StorageMemory;
use Epub\Storage\StorageZip;
use Epub\Resource\RecursiveMemory;
use Epub\Resource\ResourceMemory;
use Epub\Document\Package;
use Epub\Document\Navigation\XHTMLNavigation;

class Epub3Test extends TestCase {

    public function testIteration() {
        $iterator = new RecursiveMemory([
            new ResourceMemory('name1', null, [
                new ResourceMemory('name1.1', null, [
                    new ResourceMemory('name1.1.1', 'stuff'),
                ]),
                new ResourceMemory('name1.2', null, []),
            ]),
            new ResourceMemory('name2', 'vei'),
        ]);


        $navigation = new XHTMLNavigation();
        $package = new Package('random id', 'random title', new DateTime());
        $memoryStorage = new StorageMemory();

        $epub = (new Epub3('title'))
            ->setPackage($package)
            ->setNavigation($navigation)
            ->setCoverPage('<h1>I am the cover</h1>')
            ->setStorage($memoryStorage)
            // ->setStorage(new StorageZip('/var/www/src/book.epub'))
            ->save($iterator);

        // echo $navigation;
        // echo $package;
        // print_r($memoryStorage->getMemory());


        $this->assertArrayHasKey('META-INF', $memoryStorage->getMemory());
        $this->assertArrayHasKey('container.xml', $memoryStorage->getMemory()['META-INF']);

        $this->assertArrayHasKey('EPUB', $memoryStorage->getMemory());
        $this->assertArrayHasKey('toc.xhtml', $memoryStorage->getMemory()['EPUB']);
        $this->assertArrayHasKey('package.opf', $memoryStorage->getMemory()['EPUB']);
        $this->assertCount(4, $memoryStorage->getMemory()['EPUB']);
        $this->assertCount(3, $memoryStorage->getMemory()['EPUB']['content']);

        // $this->assertTrue(true);
    }
}