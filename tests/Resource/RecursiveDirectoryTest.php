<?php

use Epub\Resource\RecursiveDirectory;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

class RecursiveDirectoryTest extends TestCase {
    private $root;
    public function testTrue() {

        // $structure = [
        //     'root' => [
        //         'AbstractFactory' => [
        //             'test.php' => 'some text content',
        //             'other.php' => 'Some more text content',
        //             'Invalid.csv' => 'Something else',
        //         ],
        //         'AnEmptyFolder' => [],
        //         'badlocation.php' => 'some bad content',
        //     ]
        // ];
        // vfsStream::create($structure);

        // $iterator = new RecursiveDirectory((string) vfsStream::url('root'));
        // print_r($iterator);

        $this->assertTrue(true);
    }

    public function setUp(): void {
        $this->root = vfsStream::setup('root');
    }
}