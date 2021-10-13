<?php

use PHPUnit\Framework\TestCase;
use Epub\Epub3;
use Epub\Storage\StorageMemory;
use Epub\Resource\RecursiveMemory;
use Epub\Resource\ResourceMemory;
use Epub\Document\Package;
use Epub\Document\Navigation\XHTMLNavigation;
use Epub\Resource\ResourceInterface;
use Psr\Log\LoggerInterface;

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
            ->setLogger(new class implements LoggerInterface {
                public function emergency($message, array $context = [])
                {
                }
                public function alert($message, array $context = [])
                {
                }
                public function critical($message, array $context = [])
                {
                }
                public function error($message, array $context = [])
                {
                }
                public function warning($message, array $context = [])
                {
                }
                public function notice($message, array $context = [])
                {
                }
                public function info($message, array $context = [])
                {
                }
                public function debug($message, array $context = [])
                {
                }
                public function log($level, $message, array $context = [])
                {
                }
            })
            ->save($iterator);

        $this->assertArrayHasKey('META-INF', $memoryStorage->getMemory());
        $this->assertArrayHasKey('container.xml', $memoryStorage->getMemory()['META-INF']);

        $this->assertArrayHasKey('EPUB', $memoryStorage->getMemory());
        $this->assertArrayHasKey('toc.xhtml', $memoryStorage->getMemory()['EPUB']);
        $this->assertArrayHasKey('package.opf', $memoryStorage->getMemory()['EPUB']);
        $this->assertCount(4, $memoryStorage->getMemory()['EPUB']);
        $this->assertCount(3, $memoryStorage->getMemory()['EPUB']['content']);
    }

    /**
     * @dataProvider encodedContentProvider
     */
    public function testEncodeContentUri(ResourceInterface $uri, string $expected)
    {
        $encodedContentUri = (new Epub3('title'))->encodeContentUri($uri);

        $this->assertEquals($expected, $encodedContentUri);
    }

    public function encodedContentProvider(): array
    {
        ;
        return [
            [new class implements ResourceInterface
            {
                public function getContent()
                {
                    return null;
                }
                public function getName(): string
                {
                    return 'file.xml';
                }
                public function getPath(): string
                {
                    return 'onetwo';
                }
            }, 'onetwo.xhtml'],
            [new class implements ResourceInterface
            {
                public function getContent()
                {
                    return null;
                }
                public function getName(): string
                {
                    return 'file.xml';
                }
                public function getPath(): string
                {
                    return 'one two';
                }
            }, 'one-two.xhtml'],
            [new class implements ResourceInterface
            {
                public function getContent()
                {
                    return null;
                }
                public function getName(): string
                {
                    return 'file.xml';
                }
                public function getPath(): string
                {
                    return 'one-two';
                }
            }, 'one-two.xhtml'],
            [new class implements ResourceInterface
            {
                public function getContent()
                {
                    return null;
                }
                public function getName(): string
                {
                    return 'file.xml';
                }
                public function getPath(): string
                {
                    return 'one?two';
                }
            }, 'one-two.xhtml'],
            [new class implements ResourceInterface
            {
                public function getContent()
                {
                    return null;
                }
                public function getName(): string
                {
                    return 'file.xml';
                }
                public function getPath(): string
                {
                    return 'one/two';
                }
            }, 'one-two.xhtml'],
        ];
    }
}